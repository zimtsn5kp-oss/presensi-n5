<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbsencePermissionRequest;
use App\Http\Requests\ManualAttendanceRequest;
use App\Models\AbsencePermission;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\ManualAttendance;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
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
            return $this->barcodeResponse($request, false, 'Barcode tidak ditemukan pada data siswa atau guru.');
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
                'machine_user_id' => $owner->fingerprint_id,
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
                'machine_user_id' => $owner->fingerprint_id,
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

    public function manualForm()
    {
        return view('attendance.manual', ['students' => Student::where('is_active', true)->orderBy('name')->get(), 'teachers' => Teacher::where('is_active', true)->orderBy('name')->get()]);
    }

    public function storeManual(ManualAttendanceRequest $request)
    {
        $owner = $this->resolveAttendable($request->attendable_type, (int) $request->attendable_id);
        $path = $request->file('letter')?->store('manual-attendances', 'public');

        ManualAttendance::create([
            'attendable_type' => $owner::class,
            'attendable_id' => $owner->id,
            'created_by' => $request->user()->id,
            'attendance_date' => $request->attendance_date,
            'task_type' => $request->task_type,
            'description' => $request->description,
            'letter_path' => $path,
        ]);

        AttendanceLog::updateOrCreate([
            'attendable_type' => $owner::class,
            'attendable_id' => $owner->id,
            'attendance_date' => $request->attendance_date,
        ], [
            'status' => 'dispensasi',
            'source' => 'manual',
            'note' => $request->task_type.' - '.$request->description,
        ]);

        return back()->with('success', 'Kehadiran manual tersimpan.');
    }

    public function absenceForm()
    {
        return view('attendance.absence', ['students' => Student::where('is_active', true)->orderBy('name')->get(), 'teachers' => Teacher::where('is_active', true)->orderBy('name')->get()]);
    }

    public function storeAbsence(AbsencePermissionRequest $request)
    {
        $owner = $this->resolveAttendable($request->attendable_type, (int) $request->attendable_id);
        $path = $request->file('attachment')?->store('absence-permissions', 'public');

        AbsencePermission::create([
            'attendable_type' => $owner::class,
            'attendable_id' => $owner->id,
            'created_by' => $request->user()->id,
            'absence_date' => $request->absence_date,
            'type' => $request->type,
            'description' => $request->description,
            'attachment_path' => $path,
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

    private function resolveAttendable(string $type, int $id): Model
    {
        return $type === 'student' ? Student::findOrFail($id) : Teacher::findOrFail($id);
    }

    private function resolveBarcodeOwner(string $barcode): ?Model
    {
        $code = trim($barcode);
        $lowerCode = strtolower($code);
        $typedCode = preg_replace('/^(student|siswa|s):/i', '', $code);

        if ($typedCode !== $code || str_starts_with($lowerCode, 'student:') || str_starts_with($lowerCode, 'siswa:') || str_starts_with($lowerCode, 's:')) {
            return $this->findStudentByCode($typedCode);
        }

        $typedCode = preg_replace('/^(teacher|guru|t):/i', '', $code);

        if ($typedCode !== $code || str_starts_with($lowerCode, 'teacher:') || str_starts_with($lowerCode, 'guru:') || str_starts_with($lowerCode, 't:')) {
            return $this->findTeacherByCode($typedCode);
        }

        return $this->findStudentByCode($code) ?? $this->findTeacherByCode($code);
    }

    private function findStudentByCode(string $code): ?Student
    {
        return Student::where('nisn', $code)
            ->when(is_numeric($code), fn ($query) => $query->orWhere('fingerprint_id', (int) $code))
            ->first();
    }

    private function findTeacherByCode(string $code): ?Teacher
    {
        return Teacher::where('nip', $code)
            ->when(is_numeric($code), fn ($query) => $query->orWhere('fingerprint_id', (int) $code))
            ->first();
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
