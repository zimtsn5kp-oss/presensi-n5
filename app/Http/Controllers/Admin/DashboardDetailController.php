<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Services\SchoolCalendar;
use Illuminate\View\View;

class DashboardDetailController extends Controller
{
    public function __invoke(string $detail, SchoolCalendar $calendar): View
    {
        abort_unless(in_array($detail, ['late', 'absent', 'not_checked_in'], true), 404);

        return view('dashboard.detail', [
            'title' => $this->detailTitle($detail),
            'detail' => $detail,
            'rows' => $this->detailRows($detail, $calendar),
        ]);
    }

    private function detailRows(string $detail, SchoolCalendar $calendar)
    {
        if ($detail === 'not_checked_in' && $calendar->isHoliday(today())) {
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

    private function detailTitle(string $detail): string
    {
        return match ($detail) {
            'late' => 'Murid Terlambat Hari Ini',
            'absent' => 'Murid Tidak Hadir Hari Ini',
            'not_checked_in' => 'Murid Belum Absen Hari Ini',
        };
    }
}
