<?php

namespace App\Http\Resources;

use App\Models\ScheduledTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ScheduledTask
 */
class ScheduledTaskResource extends JsonResource
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
            'command' => $this->command,
            'cron_expression' => $this->cron_expression,
            'timezone' => $this->timezone,
            'enabled' => $this->enabled,
            'last_run_at' => $this->last_run_at?->toIso8601String(),
            'next_run_at' => $this->next_run_at?->toIso8601String(),
            'runs' => TaskRunResource::collection($this->whenLoaded('runs')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
