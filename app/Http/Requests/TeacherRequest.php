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
            'nip' => ['required', 'string', 'max:40', Rule::unique('teachers', 'nip')->ignore($teacher), Rule::unique('users', 'username')->ignore($teacher?->user)],
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
