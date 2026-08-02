<?php

namespace App\Http\Requests;

use App\Models\ScheduledTask;
use Cron\CronExpression;
use Illuminate\Foundation\Http\FormRequest;

class StoreScheduledTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project && $this->user()?->can('create', [ScheduledTask::class, $project]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'command' => ['required', 'string', 'max:255'],
            'cron_expression' => ['required', 'string', 'max:100', fn ($attribute, $value, $fail) => $this->validateCron($value, $fail)],
            'timezone' => ['required', 'string', 'timezone'],
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
