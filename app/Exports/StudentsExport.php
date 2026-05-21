<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Student::select('nisn', 'name', 'classroom', 'gender', 'fingerprint_id', 'is_active')->orderBy('classroom')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['NISN', 'Nama', 'Kelas', 'JK', 'Fingerprint ID', 'Aktif'];
    }
}
