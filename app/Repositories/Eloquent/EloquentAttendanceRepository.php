<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Repositories\Contracts\AttendanceRepository;
use App\Services\SchoolCalendar;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EloquentAttendanceRepository implements AttendanceRepository
{
    public function __construct(private readonly SchoolCalendar $calendar)
    {
    }

    public function dashboardMetrics(): array
    {
        $today = today();
        $activeStudents = Student::where('is_active', true)->count();
        $isHoliday = $this->calendar->isHoliday($today);
        $todayLogs = AttendanceLog::query()
            ->where('attendance_date', $today->toDateString())
            ->where('attendable_type', Student::class);

        $statusCounts = (clone $todayLogs)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $studentPresent = (int) $statusCounts->only(['hadir', 'terlambat'])->sum();
        $checkedIn = (clone $todayLogs)->distinct('attendable_id')->count('attendable_id');

        return [
            'total_students' => $activeStudents,
            'student_present' => $studentPresent,
            'late' => (int) ($statusCounts['terlambat'] ?? 0),
            'absent' => (int) $statusCounts->only(['izin', 'sakit', 'alfa'])->sum(),
            'not_checked_in' => $isHoliday ? 0 : max($activeStudents - $checkedIn, 0),
        ];
    }

    public function monthlyPercentage(?string $classroom = null): float
    {
        $students = Student::query()->where('is_active', true)->when($classroom, fn ($q) => $q->where('classroom', $classroom))->count();
        if ($students === 0) {
            return 0;
        }

        $workdays = collect(CarbonPeriod::create(now()->startOfMonth(), now()))
            ->reject(fn ($date) => $this->calendar->isHoliday($date))
            ->count();
        $present = AttendanceLog::whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->where('attendable_type', Student::class)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();

        return round(($present / max($students * $workdays, 1)) * 100, 1);
    }

    public function dailyChart(): array
    {
        $period = collect(CarbonPeriod::create(now()->subDays(6), now()));
        $rows = AttendanceLog::select(DB::raw('DATE(attendance_date) as day'), DB::raw('count(*) as total'))
            ->where('attendable_type', Student::class)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->whereBetween('attendance_date', [now()->subDays(6)->toDateString(), now()->toDateString()])
            ->groupBy('day')
            ->pluck('total', 'day');

        return [
            'labels' => $period->map(fn ($date) => $date->isoFormat('DD MMM'))->values(),
            'series' => $period->map(fn ($date) => (int) ($rows[$date->toDateString()] ?? 0))->values(),
        ];
    }

    public function weeklyLateChart(): array
    {
        $rows = AttendanceLog::select(DB::raw('DATE(attendance_date) as day'), DB::raw('count(*) as total'))
            ->where('attendable_type', Student::class)
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
