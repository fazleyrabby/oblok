<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Service
 */
class ServiceResource extends JsonResource
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
            'type' => $this->type,
            'target' => $this->target,
            'check_interval' => $this->check_interval,
            'timeout' => $this->timeout,
            'expected_status_code' => $this->expected_status_code,
            'status' => $this->status,
            'last_checked_at' => $this->last_checked_at?->toIso8601String(),
            'recent_results' => HealthCheckResultResource::collection($this->whenLoaded('healthCheckResults')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
