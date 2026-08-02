<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannelType;
use App\Models\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationChannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project && $this->user()?->can('create', [NotificationChannel::class, $project]);
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
            'type' => ['required', 'string', Rule::enum(NotificationChannelType::class)],
            'config' => ['sometimes', 'array'],
            'config.webhook_url' => ['nullable', 'string', 'url', 'max:2048'],
            'config.url' => ['nullable', 'string', 'url', 'max:2048'],
            'config.secret' => ['nullable', 'string', 'max:255'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
