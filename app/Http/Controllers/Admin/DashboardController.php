<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Repositories\Contracts\AttendanceRepository;

class DashboardController extends Controller
{
    public function __invoke(AttendanceRepository $attendance)
    {
        return view('dashboard.index', [
            'metrics' => $attendance->dashboardMetrics(),
            'dailyChart' => $attendance->dailyChart(),
            'lateChart' => $attendance->weeklyLateChart(),
            'monthlyPercentage' => $attendance->monthlyPercentage(),
            'absenceStats' => AttendanceLog::selectRaw('status, count(*) as total')
                ->where('attendable_type', Student::class)
                ->whereMonth('attendance_date', now()->month)
                ->whereIn('status', ['izin', 'sakit', 'alfa'])
                ->groupBy('status')
                ->pluck('total', 'status'),
            'activities' => ActivityLog::latest()->limit(8)->get(),
        ]);
    }
}
