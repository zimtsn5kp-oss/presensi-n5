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
            'attendable_type' => ['required', 'in:student,teacher'],
            'attendable_id' => ['required', 'integer'],
            'absence_date' => ['required', 'date'],
            'type' => ['required', 'in:izin,sakit,alfa,dispensasi'],
            'description' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ];
    }
}
