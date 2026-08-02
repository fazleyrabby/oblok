<?php

namespace App\Http\Resources;

use App\Models\HealthCheckResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HealthCheckResult
 */
class HealthCheckResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'status' => $this->status,
            'status_code' => $this->status_code,
            'response_time_ms' => $this->response_time_ms,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
