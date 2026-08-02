<?php

namespace App\Http\Resources;

use App\Models\LogEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LogEntry
 */
class LogEntryResource extends JsonResource
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
            'level' => $this->level,
            'message' => $this->message,
            'context' => $this->context,
            'channel' => $this->channel,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
