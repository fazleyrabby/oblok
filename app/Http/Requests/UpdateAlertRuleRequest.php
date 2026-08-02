<?php

namespace App\Http\Requests;

use App\Enums\AlertComparison;
use App\Enums\AlertMetric;
use App\Enums\AlertSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlertRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $alertRule = $this->route('alert_rule');

        return $alertRule && $this->user()?->can('update', $alertRule);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'metric' => ['sometimes', 'string', Rule::enum(AlertMetric::class)],
            'comparison' => ['sometimes', 'string', Rule::enum(AlertComparison::class)],
            'threshold' => ['nullable', 'integer', 'min:0'],
            'consecutive_failures' => ['nullable', 'integer', 'min:1'],
            'window_minutes' => ['sometimes', 'integer', 'min:1', 'max:43200'],
            'severity' => ['sometimes', 'string', Rule::enum(AlertSeverity::class)],
            'enabled' => ['sometimes', 'boolean'],
            'cooldown_minutes' => ['sometimes', 'integer', 'min:0', 'max:10080'],
            'channel_ids' => ['sometimes', 'array', 'max:10'],
            'channel_ids.*' => ['uuid', 'exists:notification_channels,id'],
        ];
    }
}
