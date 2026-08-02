<?php

namespace App\Http\Requests;

use App\Enums\MessagingPlatform;
use App\Models\MessagingIntegration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessagingIntegrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project && $this->user()?->can('create', [MessagingIntegration::class, $project]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', Rule::enum(MessagingPlatform::class)],
            'bot_token' => ['required', 'string', 'min:10'],
            'channel' => ['nullable', 'string', 'max:100'],
        ];
    }
}
