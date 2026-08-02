<?php

namespace App\Http\Resources;

use App\Models\NotificationDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NotificationDelivery
 */
class NotificationDeliveryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'alert_event_id' => $this->alert_event_id,
            'alert_rule_id' => $this->alert_rule_id,
            'notification_channel_id' => $this->notification_channel_id,
            'project_id' => $this->project_id,
            'severity' => $this->severity->value,
            'subject' => $this->subject,
            'status' => $this->status->value,
            'attempts' => $this->attempts,
            'last_error' => $this->last_error,
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'acknowledged_at' => $this->acknowledged_at?->toIso8601String(),
            'acknowledged_by' => $this->acknowledged_by,
            'snoozed_until' => $this->snoozed_until?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
