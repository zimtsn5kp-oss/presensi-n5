<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    public function collection(Collection $rows): void
    {
        $rows = $rows
            ->filter(fn ($row) => filled($row['nisn'] ?? null) || filled($row['nama'] ?? null))
            ->values();

        Validator::make($rows->toArray(), [
            '*.nisn' => ['required', 'string', 'max:30', 'distinct'],
            '*.nama' => ['required', 'string', 'max:150'],
            '*.kelas' => ['required', Rule::in(['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'])],
            '*.jk' => ['required', Rule::in(['L', 'P'])],
            '*.aktif' => ['nullable', Rule::in(['1', 1, '0', 0, 'ya', 'tidak', 'aktif', 'nonaktif'])],
        ], [
            '*.nisn.required' => 'Kolom nisn wajib diisi.',
            '*.nisn.distinct' => 'NISN tidak boleh duplikat di file import.',
            '*.nama.required' => 'Kolom nama wajib diisi.',
            '*.kelas.in' => 'Kolom kelas harus salah satu dari 7A, 7B, 7C, 8A, 8B, 8C, 9A, 9B, atau 9C.',
            '*.jk.in' => 'Kolom jk harus L atau P.',
        ])->validate();

        foreach ($rows as $row) {
            $student = Student::withTrashed()->firstOrNew(['nisn' => trim((string) $row['nisn'])]);
            $wasRecentlyCreated = ! $student->exists;

            if ($student->trashed()) {
                $student->restore();
            }

            $student->fill([
                'name' => trim((string) $row['nama']),
                'classroom' => trim((string) $row['kelas']),
                'gender' => strtoupper(trim((string) $row['jk'])),
                'is_active' => $this->activeValue($row['aktif'] ?? '1'),
            ]);
            $student->save();

            $wasRecentlyCreated ? $this->created++ : $this->updated++;
        }
    }

    private function activeValue(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'ya', 'aktif'], true);
    }
}
