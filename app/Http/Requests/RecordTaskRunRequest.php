<?php

namespace App\Http\Requests;

use App\Enums\TaskRunStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordTaskRunRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $scheduledTask = $this->route('scheduledTask');

        return $scheduledTask && $this->user()?->can('recordRun', $scheduledTask);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TaskRunStatus::class)],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
            'exit_code' => ['nullable', 'integer'],
            'output' => ['nullable', 'string', 'max:10000'],
            'error' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
