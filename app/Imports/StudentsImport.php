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
            ->map(fn ($row) => [
                'nisn' => $this->textValue($row['nisn'] ?? null),
                'nama' => trim((string) ($row['nama'] ?? '')),
                'kelas' => strtoupper(trim((string) ($row['kelas'] ?? ''))),
                'jk' => strtoupper(trim((string) ($row['jk'] ?? ''))),
                'aktif' => filled($row['aktif'] ?? null) ? strtolower(trim((string) $row['aktif'])) : '1',
            ])
            ->values();

        Validator::make($rows->toArray(), [
            '*.nisn' => ['required', 'max:30', 'distinct'],
            '*.nama' => ['required', 'max:150'],
            '*.kelas' => ['required', Rule::in(['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'])],
            '*.jk' => ['required', Rule::in(['L', 'P'])],
            '*.aktif' => ['nullable', Rule::in(['1', '0', 'ya', 'tidak', 'aktif', 'nonaktif'])],
        ], [
            '*.nisn.required' => 'Kolom nisn wajib diisi.',
            '*.nisn.max' => 'Kolom nisn maksimal 30 karakter.',
            '*.nisn.distinct' => 'NISN tidak boleh duplikat di file import.',
            '*.nama.required' => 'Kolom nama wajib diisi.',
            '*.nama.max' => 'Kolom nama maksimal 150 karakter.',
            '*.kelas.in' => 'Kolom kelas harus salah satu dari 7A, 7B, 7C, 8A, 8B, 8C, 9A, 9B, atau 9C.',
            '*.kelas.required' => 'Kolom kelas wajib diisi.',
            '*.jk.in' => 'Kolom jk harus L atau P.',
            '*.jk.required' => 'Kolom jk wajib diisi.',
            '*.aktif.in' => 'Kolom aktif harus 1, 0, ya, tidak, aktif, atau nonaktif.',
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

    private function textValue(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return number_format($value, 0, '', '');
        }

        return trim((string) $value);
    }
}
