<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('teachers.manage') ?? false;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'nip' => ['nullable', 'string', 'max:40', Rule::unique('teachers', 'nip')->ignore($teacher)],
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'fingerprint_id' => ['nullable', 'integer', Rule::unique('teachers', 'fingerprint_id')->ignore($teacher), Rule::unique('students', 'fingerprint_id')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
