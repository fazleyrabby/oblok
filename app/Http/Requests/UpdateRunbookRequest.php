<?php

namespace App\Http\Requests;

use App\Enums\RunbookType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRunbookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('runbook'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::enum(RunbookType::class)],
            'trigger_type' => ['required', Rule::in(['manual', 'automatic', 'both'])],
            'enabled' => ['sometimes', 'boolean'],
            'cooldown_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'timeout_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'command' => ['required_if:type,artisan,shell', 'nullable', 'string'],
            'parameters' => ['nullable', 'string'],
            'url' => ['required_if:type,webhook', 'nullable', 'url'],
            'method' => ['required_if:type,webhook', 'nullable', Rule::in(['GET', 'POST', 'PUT', 'DELETE'])],
            'headers' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
        ];
    }
}
