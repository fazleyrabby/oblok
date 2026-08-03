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
            'type' => ['sometimes', 'required', 'string', 'in:http,tcp,tls,dns'],
            'target' => ['sometimes', 'required', 'string', 'max:2048'],
            'check_interval' => ['sometimes', 'nullable', 'integer', 'min:10', 'max:86400'],
            'timeout' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:60'],
            'expected_status_code' => ['sometimes', 'nullable', 'integer', 'min:100', 'max:599'],
            'config' => ['sometimes', 'nullable', 'array'],
            'config.expected_body_pattern' => ['nullable', 'string', 'max:1000'],
            'config.expected_headers' => ['nullable', 'array'],
            'config.min_cert_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'config.record_type' => ['nullable', 'string', 'in:A,AAAA,CNAME,MX,TXT'],
            'config.expected_value' => ['nullable', 'string', 'max:255'],
        ];
    }
}
