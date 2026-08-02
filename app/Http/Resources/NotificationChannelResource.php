<?php

namespace App\Http\Resources;

use App\Models\NotificationChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NotificationChannel
 */
class NotificationChannelResource extends JsonResource
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
            'type' => $this->type->value,
            'enabled' => $this->enabled,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
