<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Repositories\Contracts\AttendanceRepository;
use App\Services\SchoolCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AttendanceRepository $attendance, SchoolCalendar $calendar)
    {
        $filters = $request->validate([
            'classroom' => ['nullable', Rule::in($this->classrooms())],
        ]);
        $classroom = $filters['classroom'] ?? null;
        $metrics = $attendance->dashboardMetrics();
        $attendancePercentage = $metrics['total_students'] > 0
            ? round(($metrics['student_present'] / $metrics['total_students']) * 100, 1)
            : 0;

        return view('dashboard.index', [
            'metrics' => $metrics,
            'attendancePercentage' => $attendancePercentage,
            'classrooms' => $this->classrooms(),
            'selectedClassroom' => $classroom,
            'statusRows' => $this->statusRows($classroom, $calendar),
        ]);
    }

    private function statusRows(?string $classroom, SchoolCalendar $calendar)
    {
        $studentTotals = Student::query()
            ->select('classroom', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->when($classroom, fn ($query) => $query->where('classroom', $classroom))
            ->groupBy('classroom')
            ->orderBy('classroom')
            ->pluck('total', 'classroom');

        $logsByClassroom = AttendanceLog::query()
            ->join('students', 'students.id', '=', 'attendance_logs.attendable_id')
            ->select('students.classroom', 'attendance_logs.status', DB::raw('count(*) as total'))
            ->where('attendance_logs.attendable_type', Student::class)
            ->where('attendance_logs.attendance_date', today()->toDateString())
            ->where('students.is_active', true)
            ->when($classroom, fn ($query) => $query->where('students.classroom', $classroom))
            ->groupBy('students.classroom', 'attendance_logs.status')
            ->get()
            ->groupBy('classroom');

        $checkedByClassroom = AttendanceLog::query()
            ->join('students', 'students.id', '=', 'attendance_logs.attendable_id')
            ->select('students.classroom', DB::raw('count(distinct attendance_logs.attendable_id) as total'))
            ->where('attendance_logs.attendable_type', Student::class)
            ->where('attendance_logs.attendance_date', today()->toDateString())
            ->where('students.is_active', true)
            ->when($classroom, fn ($query) => $query->where('students.classroom', $classroom))
            ->groupBy('students.classroom')
            ->pluck('total', 'classroom');

        return $studentTotals
            ->map(function (int $total, string $classroom) use ($logsByClassroom, $checkedByClassroom, $calendar) {
                $statusCounts = $logsByClassroom->get($classroom, collect())->pluck('total', 'status');
                $checkedCount = (int) ($checkedByClassroom[$classroom] ?? 0);

                return [
                    'classroom' => $classroom,
                    'total' => $total,
                    'present' => (int) ($statusCounts['hadir'] ?? 0),
                    'late' => (int) ($statusCounts['terlambat'] ?? 0),
                    'absent' => (int) $statusCounts->only(['izin', 'sakit', 'alfa'])->sum(),
                    'not_checked_in' => $calendar->isHoliday(today()) ? 0 : max($total - $checkedCount, 0),
                ];
            })
            ->values();
    }

    private function classrooms(): array
    {
        return ['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'];
    }
}
