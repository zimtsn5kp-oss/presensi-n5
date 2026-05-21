<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Fingerprint;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class FingerprintApiController extends Controller
{
    public function ping(Fingerprint $fingerprint)
    {
        $fingerprint->update(['is_online' => true, 'last_sync_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Mesin terhubung.', 'data' => ['machine' => $fingerprint->name]]);
    }

    public function storeLog(Request $request, Fingerprint $fingerprint)
    {
        $data = $request->validate([
            'machine_user_id' => ['required', 'integer'],
            'attendance_date' => ['required', 'date'],
            'check_in_at' => ['nullable', 'date_format:H:i:s'],
            'check_out_at' => ['nullable', 'date_format:H:i:s'],
            'status' => ['nullable', 'in:hadir,terlambat,izin,sakit,alfa,dispensasi'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'raw_payload' => ['nullable', 'array'],
        ]);

        $owner = Student::where('fingerprint_id', $data['machine_user_id'])->first()
            ?? Teacher::where('fingerprint_id', $data['machine_user_id'])->first();

        if (! $owner) {
            return response()->json(['success' => false, 'message' => 'Fingerprint ID belum direlasikan.'], 422);
        }

        $log = AttendanceLog::updateOrCreate([
            'attendable_type' => $owner::class,
            'attendable_id' => $owner->id,
            'attendance_date' => $data['attendance_date'],
        ], [
            'fingerprint_id' => $fingerprint->id,
            'machine_user_id' => $data['machine_user_id'],
            'check_in_at' => $data['check_in_at'] ?? null,
            'check_out_at' => $data['check_out_at'] ?? null,
            'status' => $data['status'] ?? 'hadir',
            'late_minutes' => $data['late_minutes'] ?? 0,
            'source' => 'api-fingerprint',
            'raw_payload' => $data['raw_payload'] ?? $request->all(),
        ]);

        return response()->json(['success' => true, 'message' => 'Log absensi tersimpan.', 'data' => $log]);
    }
}
