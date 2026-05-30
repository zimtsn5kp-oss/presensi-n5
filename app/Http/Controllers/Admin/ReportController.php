<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'status']);
        $logs = $this->query($filters)->paginate(25)->withQueryString();

        return view('reports.index', compact('logs', 'filters'));
    }

    public function excel(Request $request)
    {
        return Excel::download(new AttendanceReportExport($request->only(['date_from', 'date_to', 'status'])), 'laporan-absensi.xlsx');
    }

    public function pdf(Request $request)
    {
        return Pdf::loadView('exports.attendance', ['logs' => $this->query($request->only(['date_from', 'date_to', 'status']))->get()])
            ->download('laporan-absensi.pdf');
    }

    private function query(array $filters)
    {
        return AttendanceLog::with('attendable')
            ->where('attendable_type', Student::class)
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '<=', $date))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('attendance_date');
    }
}
