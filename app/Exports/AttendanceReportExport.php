<?php

namespace App\Exports;

use App\Models\AttendanceLog;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceReportExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return AttendanceLog::query()
            ->with('attendable')
            ->where('attendable_type', Student::class)
            ->when($this->filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '>=', $date))
            ->when($this->filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '<=', $date))
            ->when($this->filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('attendance_date')
            ->get()
            ->map(fn ($log) => [
                'date' => $log->attendance_date?->format('Y-m-d'),
                'name' => $log->attendable?->name ?? 'Tidak dikenali',
                'check_in' => $log->check_in_at,
                'check_out' => $log->check_out_at,
                'status' => ucfirst($log->status),
                'late_minutes' => $log->late_minutes,
                'source' => $log->source,
            ]);
    }

    public function headings(): array
    {
        return ['Tanggal', 'Nama Siswa', 'Masuk', 'Pulang', 'Status', 'Terlambat Menit', 'Sumber'];
    }
}
