<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Repositories\Contracts\AttendanceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AttendanceRepository $attendance)
    {
        $filters = $request->validate([
            'classroom' => ['nullable', Rule::in($this->classrooms())],
        ]);
        $classroom = $filters['classroom'] ?? null;

        return view('dashboard.index', [
            'metrics' => $attendance->dashboardMetrics(),
            'classrooms' => $this->classrooms(),
            'selectedClassroom' => $classroom,
            'statusChart' => $this->statusChart($classroom),
        ]);
    }

    private function statusChart(?string $classroom): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $studentIds = Student::query()
            ->where('is_active', true)
            ->when($classroom, fn ($query) => $query->where('classroom', $classroom))
            ->pluck('id');

        $logs = AttendanceLog::query()
            ->where('attendable_type', Student::class)
            ->whereIn('attendable_id', $studentIds)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($log) => $log->attendance_date->format('Y-m-d'));

        $dates = collect(range(1, $end->day))->map(fn (int $day) => Carbon::create($end->year, $end->month, $day));
        $count = fn ($dailyLogs, array $statuses) => $dailyLogs?->whereIn('status', $statuses)->count() ?? 0;

        return [
            'title' => $start->isoFormat('MMMM YYYY'),
            'labels' => $dates->map(fn ($date) => $date->format('d'))->values(),
            'series' => [
                ['name' => 'Kehadiran', 'data' => $dates->map(fn ($date) => $count($logs->get($date->format('Y-m-d')), ['hadir', 'terlambat']))->values()],
                ['name' => 'Sakit', 'data' => $dates->map(fn ($date) => $count($logs->get($date->format('Y-m-d')), ['sakit']))->values()],
                ['name' => 'Izin', 'data' => $dates->map(fn ($date) => $count($logs->get($date->format('Y-m-d')), ['izin']))->values()],
                ['name' => 'Alfa', 'data' => $dates->map(fn ($date) => $count($logs->get($date->format('Y-m-d')), ['alfa']))->values()],
            ],
        ];
    }

    private function classrooms(): array
    {
        return ['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'];
    }
}
