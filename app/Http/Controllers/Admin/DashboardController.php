<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Repositories\Contracts\AttendanceRepository;
use App\Services\SchoolCalendar;
use Illuminate\Http\Request;
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
        $students = Student::query()
            ->where('is_active', true)
            ->when($classroom, fn ($query) => $query->where('classroom', $classroom))
            ->orderBy('classroom')
            ->get();

        $logs = AttendanceLog::query()
            ->where('attendable_type', Student::class)
            ->whereDate('attendance_date', today())
            ->whereIn('attendable_id', $students->pluck('id'))
            ->get()
            ->groupBy('attendable_id');

        return $students
            ->groupBy('classroom')
            ->map(function ($classStudents, string $classroom) use ($logs, $calendar) {
                $classLogs = $classStudents
                    ->flatMap(fn ($student) => $logs->get($student->id, collect()))
                    ->values();
                $checkedCount = $classLogs->pluck('attendable_id')->unique()->count();

                return [
                    'classroom' => $classroom,
                    'total' => $classStudents->count(),
                    'present' => $classLogs->where('status', 'hadir')->count(),
                    'late' => $classLogs->where('status', 'terlambat')->count(),
                    'absent' => $classLogs->whereIn('status', ['izin', 'sakit', 'alfa'])->count(),
                    'not_checked_in' => $calendar->isHoliday(today()) ? 0 : max($classStudents->count() - $checkedCount, 0),
                ];
            })
            ->values();
    }

    private function classrooms(): array
    {
        return ['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'];
    }
}
