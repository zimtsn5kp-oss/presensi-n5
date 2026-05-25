<?php

namespace App\Imports;

use App\Models\Teacher;
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
            ->values();

        Validator::make($rows->toArray(), [
            '*.nip' => ['nullable', 'string', 'max:40', 'distinct'],
            '*.nama' => ['required', 'string', 'max:150'],
            '*.mapel' => ['nullable', 'string', 'max:100'],
            '*.aktif' => ['nullable', Rule::in(['1', 1, '0', 0, 'ya', 'tidak', 'aktif', 'nonaktif'])],
        ], [
            '*.nip.distinct' => 'NIP tidak boleh duplikat di file import.',
            '*.nama.required' => 'Kolom nama wajib diisi.',
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

            $wasRecentlyCreated ? $this->created++ : $this->updated++;
        }
    }

    private function activeValue(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'ya', 'aktif'], true);
    }
}
