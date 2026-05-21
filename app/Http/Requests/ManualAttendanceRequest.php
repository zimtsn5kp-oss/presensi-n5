<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('attendance.manual') ?? false;
    }

    public function rules(): array
    {
        return [
            'attendable_type' => ['required', 'in:student,teacher'],
            'attendable_id' => ['required', 'integer'],
            'attendance_date' => ['required', 'date'],
            'task_type' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'letter' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ];
    }
}
