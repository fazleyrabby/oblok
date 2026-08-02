<?php

namespace App\Http\Requests;

use App\Models\NotificationDelivery;
use Illuminate\Foundation\Http\FormRequest;

class SnoozeNotificationDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $delivery = $this->route('delivery');

        return $delivery instanceof NotificationDelivery
            && $this->user()?->can('update', $delivery->project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'until' => ['sometimes', 'date', 'after:now'],
        ];
    }
}
