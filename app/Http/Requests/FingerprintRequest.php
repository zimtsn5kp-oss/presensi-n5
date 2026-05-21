<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FingerprintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('fingerprints.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'ip_address' => ['required', 'ip'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'serial_number' => ['nullable', 'string', 'max:120'],
        ];
    }
}
