<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'in:http,tcp,icmp'],
            'target' => ['sometimes', 'required', 'string', 'max:2048'],
            'check_interval' => ['sometimes', 'integer', 'min:10', 'max:86400'],
            'timeout' => ['sometimes', 'integer', 'min:1', 'max:60'],
            'expected_status_code' => ['sometimes', 'integer', 'min:100', 'max:599'],
        ];
    }
}
