<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:http,tcp,tls,dns'],
            'target' => ['required', 'string', 'max:2048'],
            'check_interval' => ['nullable', 'integer', 'min:10', 'max:86400'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:60'],
            'expected_status_code' => ['nullable', 'integer', 'min:100', 'max:599'],
            'config' => ['nullable', 'array'],
            'config.expected_body_pattern' => ['nullable', 'string', 'max:1000'],
            'config.expected_headers' => ['nullable', 'array'],
            'config.min_cert_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'config.record_type' => ['nullable', 'string', 'in:A,AAAA,CNAME,MX,TXT'],
            'config.expected_value' => ['nullable', 'string', 'max:255'],
        ];
    }
}
