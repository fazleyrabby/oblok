<?php

namespace App\Http\Resources;

use App\Models\AlertEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AlertEvent
 */
class AlertEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'alert_rule_id' => $this->alert_rule_id,
            'project_id' => $this->project_id,
            'severity' => $this->severity->value,
            'subject' => $this->subject,
            'context' => $this->context,
            'triggered_at' => $this->triggered_at->toIso8601String(),
            'deliveries' => NotificationDeliveryResource::collection($this->whenLoaded('deliveries')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
