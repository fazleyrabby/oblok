<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationChannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $channel = $this->route('notification_channel');

        return $channel && $this->user()?->can('update', $channel);
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
            'type' => ['sometimes', 'string', Rule::enum(NotificationChannelType::class)],
            'config' => ['sometimes', 'array'],
            'config.webhook_url' => ['nullable', 'string', 'url', 'max:2048'],
            'config.url' => ['nullable', 'string', 'url', 'max:2048'],
            'config.secret' => ['nullable', 'string', 'max:255'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
