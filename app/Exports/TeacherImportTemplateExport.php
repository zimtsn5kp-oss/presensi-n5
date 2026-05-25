<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeacherImportTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['198812122010011001', 'Ust. Hasan Basri', 'Fiqih', '1'],
            ['199105052014012002', 'Usth. Nur Aulia', 'Bahasa Arab', '1'],
        ];
    }

    public function headings(): array
    {
        return ['nip', 'nama', 'mapel', 'aktif'];
    }
}
