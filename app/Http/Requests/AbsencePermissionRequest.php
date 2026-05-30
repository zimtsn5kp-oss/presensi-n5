<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbsencePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('attendance.absence') ?? false;
    }

    public function rules(): array
    {
        return [
            'attendable_id' => ['required', 'integer', 'exists:students,id'],
            'absence_date' => ['required', 'date'],
            'type' => ['required', 'in:izin,sakit,alfa'],
            'description' => ['nullable', 'string'],
        ];
    }
}
