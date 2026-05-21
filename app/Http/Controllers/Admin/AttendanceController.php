<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbsencePermissionRequest;
use App\Http\Requests\ManualAttendanceRequest;
use App\Models\AbsencePermission;
use App\Models\AttendanceLog;
use App\Models\ManualAttendance;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

class AttendanceController extends Controller
{
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
}
