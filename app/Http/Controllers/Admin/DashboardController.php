<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Repositories\Contracts\AttendanceRepository;
use App\Services\SchoolCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AttendanceRepository $attendance, SchoolCalendar $calendar)
    {
        $filters = $request->validate([
            'classroom' => ['nullable', Rule::in($this->classrooms())],
            'detail' => ['nullable', Rule::in(['late', 'absent', 'not_checked_in'])],
        ]);
        $classroom = $filters['classroom'] ?? null;
        $detail = $filters['detail'] ?? null;

        return view('dashboard.index', [
            'metrics' => $attendance->dashboardMetrics(),
            'classrooms' => $this->classrooms(),
            'selectedClassroom' => $classroom,
            'statusChart' => $this->statusChart($classroom),
            'detailType' => $detail,
            'detailTitle' => $this->detailTitle($detail),
            'detailRows' => $this->detailRows($detail, $calendar),
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

    private function detailRows(?string $detail, SchoolCalendar $calendar)
    {
        if (! $detail || ($detail === 'not_checked_in' && $calendar->isHoliday(today()))) {
            return collect();
        }

        if ($detail === 'not_checked_in') {
            return Student::query()
                ->where('is_active', true)
                ->whereDoesntHave('attendanceLogs', fn ($query) => $query->whereDate('attendance_date', today()))
                ->orderBy('classroom')
                ->orderBy('name')
                ->get()
                ->map(fn ($student) => [
                    'student' => $student,
                    'status' => 'Belum absen',
                    'check_in_at' => null,
                ]);
        }

        $statuses = $detail === 'late' ? ['terlambat'] : ['izin', 'sakit', 'alfa'];

        return AttendanceLog::query()
            ->with('attendable')
            ->where('attendable_type', Student::class)
            ->whereDate('attendance_date', today())
            ->whereIn('status', $statuses)
            ->get()
            ->filter(fn ($log) => $log->attendable?->is_active)
            ->sortBy(fn ($log) => $log->attendable->classroom.'-'.$log->attendable->name)
            ->values()
            ->map(fn ($log) => [
                'student' => $log->attendable,
                'status' => ucfirst($log->status),
                'check_in_at' => $log->check_in_at,
            ]);
    }

    private function detailTitle(?string $detail): ?string
    {
        return match ($detail) {
            'late' => 'Murid Terlambat Hari Ini',
            'absent' => 'Murid Tidak Hadir Hari Ini',
            'not_checked_in' => 'Murid Belum Absen Hari Ini',
            default => null,
        };
    }

    private function classrooms(): array
    {
        return ['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'];
    }
}
