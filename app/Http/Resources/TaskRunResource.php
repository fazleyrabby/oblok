<?php

namespace App\Http\Resources;

use App\Models\TaskRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TaskRun
 */
class TaskRunResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scheduled_task_id' => $this->scheduled_task_id,
            'status' => $this->status->value,
            'started_at' => $this->started_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'duration_ms' => $this->duration_ms,
            'exit_code' => $this->exit_code,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
