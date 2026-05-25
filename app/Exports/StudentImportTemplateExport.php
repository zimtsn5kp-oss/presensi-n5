<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentImportTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['0061234501', 'Ahmad Fauzan', '7A', 'L', '1'],
            ['0061234502', 'Aisyah Zahra', '7A', 'P', '1'],
        ];
    }

    public function headings(): array
    {
        return ['nisn', 'nama', 'kelas', 'jk', 'aktif'];
    }
}
