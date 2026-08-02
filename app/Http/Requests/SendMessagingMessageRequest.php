<?php

namespace App\Http\Requests;

use App\Models\MessagingIntegration;
use Illuminate\Foundation\Http\FormRequest;

class SendMessagingMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $integration = $this->route('integration');

        return $integration instanceof MessagingIntegration
            && $this->user()?->can('send', $integration);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'channel' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:4000'],
        ];
    }
}
