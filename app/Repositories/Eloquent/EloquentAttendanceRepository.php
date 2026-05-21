<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\Contracts\AttendanceRepository;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EloquentAttendanceRepository implements AttendanceRepository
{
    public function dashboardMetrics(): array
    {
        $today = today();
        $activeStudents = Student::where('is_active', true)->count();
        $activeTeachers = Teacher::where('is_active', true)->count();

        $studentPresent = AttendanceLog::whereDate('attendance_date', $today)
            ->where('attendable_type', Student::class)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();

        return [
            'student_present' => $studentPresent,
            'late' => AttendanceLog::whereDate('attendance_date', $today)->where('status', 'terlambat')->count(),
            'absent' => AttendanceLog::whereDate('attendance_date', $today)->whereIn('status', ['izin', 'sakit', 'alfa'])->count(),
            'teacher_present' => AttendanceLog::whereDate('attendance_date', $today)->where('attendable_type', Teacher::class)->whereIn('status', ['hadir', 'terlambat'])->count(),
            'not_checked_in' => max(($activeStudents + $activeTeachers) - AttendanceLog::whereDate('attendance_date', $today)->distinct('attendable_id')->count('attendable_id'), 0),
        ];
    }

    public function monthlyPercentage(?string $classroom = null): float
    {
        $students = Student::query()->where('is_active', true)->when($classroom, fn ($q) => $q->where('classroom', $classroom))->count();
        if ($students === 0) {
            return 0;
        }

        $workdays = collect(CarbonPeriod::create(now()->startOfMonth(), now()))->filter(fn ($date) => $date->isWeekday())->count();
        $present = AttendanceLog::whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->where('attendable_type', Student::class)
            ->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])
            ->count();

        return round(($present / max($students * $workdays, 1)) * 100, 1);
    }

    public function dailyChart(): array
    {
        $period = collect(CarbonPeriod::create(now()->subDays(6), now()));

        return [
            'labels' => $period->map(fn ($date) => $date->isoFormat('DD MMM'))->values(),
            'series' => $period->map(fn ($date) => AttendanceLog::whereDate('attendance_date', $date)->whereIn('status', ['hadir', 'terlambat'])->count())->values(),
        ];
    }

    public function weeklyLateChart(): array
    {
        $rows = AttendanceLog::select(DB::raw('DATE(attendance_date) as day'), DB::raw('count(*) as total'))
            ->where('status', 'terlambat')
            ->whereBetween('attendance_date', [now()->subDays(6)->toDateString(), Carbon::now()->toDateString()])
            ->groupBy('day')
            ->pluck('total', 'day');

        $period = collect(CarbonPeriod::create(now()->subDays(6), now()));

        return [
            'labels' => $period->map(fn ($date) => $date->isoFormat('DD MMM'))->values(),
            'series' => $period->map(fn ($date) => (int) ($rows[$date->toDateString()] ?? 0))->values(),
        ];
    }
}
