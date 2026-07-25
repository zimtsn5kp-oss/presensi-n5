<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Services\TeacherAccountService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeachersImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    public function collection(Collection $rows): void
    {
        $rows = $rows
            ->filter(fn ($row) => filled($row['nip'] ?? null) || filled($row['nama'] ?? null))
            ->map(fn ($row) => [
                'nip' => $this->textValue($row['nip'] ?? null),
                'nama' => trim((string) ($row['nama'] ?? '')),
                'mapel' => trim((string) ($row['mapel'] ?? '')),
                'aktif' => filled($row['aktif'] ?? null) ? strtolower(trim((string) $row['aktif'])) : '1',
            ])
            ->values();

        Validator::make($rows->toArray(), [
            '*.nip' => ['required', 'max:40', 'distinct'],
            '*.nama' => ['required', 'max:150'],
            '*.mapel' => ['nullable', 'max:100'],
            '*.aktif' => ['nullable', Rule::in(['1', '0', 'ya', 'tidak', 'aktif', 'nonaktif'])],
        ], [
            '*.nip.required' => 'Kolom NIP wajib diisi untuk membuat akun login guru.',
            '*.nip.max' => 'Kolom NIP maksimal 40 karakter.',
            '*.nip.distinct' => 'NIP tidak boleh duplikat di file import.',
            '*.nama.required' => 'Kolom nama wajib diisi.',
            '*.nama.max' => 'Kolom nama maksimal 150 karakter.',
            '*.mapel.max' => 'Kolom mapel maksimal 100 karakter.',
            '*.aktif.in' => 'Kolom aktif harus 1, 0, ya, tidak, aktif, atau nonaktif.',
        ])->validate();

        foreach ($rows as $row) {
            $nip = trim((string) ($row['nip'] ?? ''));
            $query = Teacher::withTrashed();
            $teacher = filled($nip) ? $query->firstOrNew(['nip' => $nip]) : new Teacher();
            $wasRecentlyCreated = ! $teacher->exists;

            if ($teacher->trashed()) {
                $teacher->restore();
            }

            $teacher->fill([
                'nip' => filled($nip) ? $nip : null,
                'name' => trim((string) $row['nama']),
                'subject' => filled($row['mapel'] ?? null) ? trim((string) $row['mapel']) : null,
                'is_active' => $this->activeValue($row['aktif'] ?? '1'),
            ]);
            $teacher->save();
            app(TeacherAccountService::class)->sync($teacher);

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
