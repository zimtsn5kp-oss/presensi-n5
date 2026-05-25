<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('students.manage') ?? false;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'nisn' => ['required', 'string', 'max:30', Rule::unique('students', 'nisn')->ignore($student)],
            'name' => ['required', 'string', 'max:150'],
            'classroom' => ['required', Rule::in(['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'])],
            'gender' => ['required', 'in:L,P'],
            'fingerprint_id' => ['nullable', 'integer', Rule::unique('students', 'fingerprint_id')->ignore($student), Rule::unique('teachers', 'fingerprint_id')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
