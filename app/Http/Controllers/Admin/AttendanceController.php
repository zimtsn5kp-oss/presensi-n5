<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbsencePermissionRequest;
use App\Models\AbsencePermission;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\Student;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function barcodeForm()
    {
        return view('attendance.barcode');
    }

    public function storeBarcode(Request $request)
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:80'],
            'attendance_type' => ['nullable', 'in:auto,check_in,check_out'],
        ], [
            'barcode.required' => 'Kode barcode belum terbaca.',
        ]);

        $owner = $this->resolveBarcodeOwner($data['barcode']);

        if (! $owner) {
            return $this->barcodeResponse($request, false, 'Barcode tidak ditemukan pada data siswa.');
        }

        if (! $owner->is_active) {
            return $this->barcodeResponse($request, false, $owner->name.' sedang nonaktif.');
        }

        $now = now();
        $today = $now->toDateString();
        $time = $now->format('H:i:s');
        $mode = $data['attendance_type'] ?? 'auto';
        $log = AttendanceLog::firstOrNew([
            'attendable_type' => $owner::class,
            'attendable_id' => $owner->id,
            'attendance_date' => $today,
        ]);

        $action = $this->resolveBarcodeAction($log, $mode);

        if (! $action) {
            return $this->barcodeResponse($request, false, $owner->name.' sudah memiliki absen masuk dan pulang hari ini.', $log);
        }

        if ($action === 'check_in') {
            $lateMinutes = $this->lateMinutes($now);
            $log->fill([
                'check_in_at' => $time,
                'status' => $lateMinutes > 0 ? 'terlambat' : 'hadir',
                'late_minutes' => $lateMinutes,
                'source' => 'barcode',
                'note' => null,
                'raw_payload' => [
                    'barcode' => trim($data['barcode']),
                    'scanner' => 'webcam-or-usb',
                    'operator_id' => $request->user()?->id,
                ],
            ]);
        }

        if ($action === 'check_out') {
            if (! $log->exists || blank($log->check_in_at)) {
                return $this->barcodeResponse($request, false, 'Absen masuk belum tercatat untuk '.$owner->name.'.');
            }

            $log->fill([
                'check_out_at' => $time,
                'source' => 'barcode',
                'raw_payload' => array_merge($log->raw_payload ?? [], [
                    'last_barcode' => trim($data['barcode']),
                    'last_scanner' => 'webcam-or-usb',
                    'last_operator_id' => $request->user()?->id,
                ]),
            ]);
        }

        $log->save();

        $label = $action === 'check_in' ? 'masuk' : 'pulang';

        return $this->barcodeResponse($request, true, 'Absen '.$label.' tersimpan untuk '.$owner->name.'.', $log);
    }

    public function absenceForm()
    {
        return view('attendance.absence', ['students' => Student::where('is_active', true)->orderBy('name')->get()]);
    }

    public function storeAbsence(AbsencePermissionRequest $request)
    {
        $owner = Student::findOrFail((int) $request->attendable_id);

        AbsencePermission::create([
            'attendable_type' => $owner::class,
            'attendable_id' => $owner->id,
            'created_by' => $request->user()->id,
            'absence_date' => $request->absence_date,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        AttendanceLog::updateOrCreate([
            'attendable_type' => $owner::class,
            'attendable_id' => $owner->id,
            'attendance_date' => $request->absence_date,
        ], [
            'status' => $request->type,
            'source' => 'manual',
            'note' => $request->description,
        ]);

        return back()->with('success', 'Data ketidakhadiran tersimpan.');
    }

    private function resolveBarcodeOwner(string $barcode): ?Student
    {
        $code = trim($barcode);
        $typedCode = preg_replace('/^(student|siswa|s):/i', '', $code);

        return $this->findStudentByCode($typedCode);
    }

    private function findStudentByCode(string $code): ?Student
    {
        return Student::where('nisn', $code)->first();
    }

    private function resolveBarcodeAction(AttendanceLog $log, string $mode): ?string
    {
        if ($mode === 'check_in') {
            return filled($log->check_in_at) ? null : 'check_in';
        }

        if ($mode === 'check_out') {
            return filled($log->check_out_at) ? null : 'check_out';
        }

        if (blank($log->check_in_at)) {
            return 'check_in';
        }

        return blank($log->check_out_at) ? 'check_out' : null;
    }

    private function lateMinutes(CarbonInterface $now): int
    {
        $setting = AttendanceSetting::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->where('day_of_week', $now->dayOfWeek)->orWhereNull('day_of_week');
            })
            ->orderByRaw('day_of_week is null')
            ->first();

        if (! $setting?->late_after) {
            return 0;
        }

        $lateAfter = $now->copy()->setTimeFromTimeString($setting->late_after);

        return $now->greaterThan($lateAfter) ? (int) $lateAfter->diffInMinutes($now) : 0;
    }

    private function barcodeResponse(Request $request, bool $success, string $message, ?AttendanceLog $log = null)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $log ? [
                    'name' => $log->attendable?->name,
                    'date' => $log->attendance_date?->format('Y-m-d'),
                    'check_in_at' => $log->check_in_at,
                    'check_out_at' => $log->check_out_at,
                    'status' => $log->status,
                    'late_minutes' => $log->late_minutes,
                ] : null,
            ], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
