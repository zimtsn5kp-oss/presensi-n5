<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeachersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Teacher::select('nip', 'name', 'subject', 'position', 'fingerprint_id', 'is_active')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['NIP', 'Nama', 'Mapel', 'Jabatan', 'Fingerprint ID', 'Aktif'];
    }
}
