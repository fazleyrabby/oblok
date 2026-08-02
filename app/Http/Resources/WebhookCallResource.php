<?php

namespace App\Http\Resources;

use App\Models\WebhookCall;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WebhookCall
 */
class WebhookCallResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $withDetails = $request->routeIs('*.show') || $request->routeIs('*.replay');

        return array_merge([
            'id' => $this->id,
            'project_id' => $this->project_id,
            'event' => $this->event,
            'method' => $this->method,
            'url' => $this->url,
            'status_code' => $this->status_code,
            'processing_time_ms' => $this->processing_time_ms,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'replay_count' => $this->replay_count,
            'replayed_at' => $this->replayed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ], $withDetails ? [
            'request_headers' => $this->request_headers,
            'request_payload' => $this->request_payload,
            'response_payload' => $this->response_payload,
        ] : []);
    }
}
