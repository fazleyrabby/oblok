<?php

namespace App\Http\Requests;

use Cron\CronExpression;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduledTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $scheduledTask = $this->route('scheduledTask');

        return $scheduledTask && $this->user()?->can('update', $scheduledTask);
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
            'command' => ['sometimes', 'string', 'max:255'],
            'cron_expression' => ['sometimes', 'string', 'max:100', fn ($attribute, $value, $fail) => $this->validateCron($value, $fail)],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }

    private function validateCron(string $value, callable $fail): void
    {
        if (! CronExpression::isValidExpression($value)) {
            $fail('The cron expression is invalid.');
        }
    }
}
