<?php

namespace App\Http\Requests;

use App\Models\LogEntry;
use Illuminate\Foundation\Http\FormRequest;

class IngestLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project && $this->user()?->can('create', [LogEntry::class, $project]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'level' => ['nullable', 'string', 'in:debug,info,notice,warning,error,critical,alert,emergency'],
            'message' => ['required', 'string', 'max:10000'],
            'context' => ['nullable', 'array'],
            'channel' => ['nullable', 'string', 'max:50'],
        ];
    }
}
