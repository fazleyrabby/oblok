<?php

namespace App\Http\Resources;

use App\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AlertRule
 */
class AlertRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'description' => $this->description,
            'metric' => $this->metric->value,
            'comparison' => $this->comparison->value,
            'threshold' => $this->threshold,
            'consecutive_failures' => $this->consecutive_failures,
            'window_minutes' => $this->window_minutes,
            'severity' => $this->severity->value,
            'enabled' => $this->enabled,
            'cooldown_minutes' => $this->cooldown_minutes,
            'last_evaluated_at' => $this->last_evaluated_at?->toIso8601String(),
            'last_triggered_at' => $this->last_triggered_at?->toIso8601String(),
            'channels' => NotificationChannelResource::collection($this->whenLoaded('channels')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
