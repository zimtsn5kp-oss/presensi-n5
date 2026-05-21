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
            'classroom' => ['required', 'string', 'max:50'],
            'gender' => ['required', 'in:L,P'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'fingerprint_id' => ['nullable', 'integer', Rule::unique('students', 'fingerprint_id')->ignore($student)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
