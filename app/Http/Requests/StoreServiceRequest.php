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
            'type' => ['required', 'string', 'in:http,tcp,icmp'],
            'target' => ['required', 'string', 'max:2048'],
            'check_interval' => ['integer', 'min:10', 'max:86400'],
            'timeout' => ['integer', 'min:1', 'max:60'],
            'expected_status_code' => ['integer', 'min:100', 'max:599'],
        ];
    }
}
