<?php

namespace App\Http\Resources;

use App\Models\MessagingIntegration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MessagingIntegration
 */
class MessagingIntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'platform' => $this->platform->value,
            'platform_label' => $this->platform->label(),
            'name' => $this->name,
            'channel' => $this->channel,
            'enabled' => $this->enabled,
            'last_connected_at' => $this->last_connected_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
