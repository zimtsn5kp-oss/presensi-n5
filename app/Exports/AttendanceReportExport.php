<?php

namespace App\Exports;

use App\Models\AttendanceLog;
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
            ->when($this->filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '>=', $date))
            ->when($this->filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '<=', $date))
            ->latest('attendance_date')
            ->get()
            ->map(fn ($log) => [
                'date' => $log->attendance_date?->format('Y-m-d'),
                'name' => $log->attendable?->name ?? 'Tidak dikenali',
                'type' => class_basename($log->attendable_type),
                'check_in' => $log->check_in_at,
                'check_out' => $log->check_out_at,
                'status' => ucfirst($log->status),
                'late_minutes' => $log->late_minutes,
                'source' => $log->source,
            ]);
    }

    public function headings(): array
    {
        return ['Tanggal', 'Nama', 'Tipe', 'Masuk', 'Pulang', 'Status', 'Terlambat Menit', 'Sumber'];
    }
}
