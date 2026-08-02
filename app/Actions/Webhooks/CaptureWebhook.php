<?php

namespace App\Actions\Webhooks;

use App\Models\Project;
use App\Models\WebhookCall;

class CaptureWebhook
{
    /**
     * Persist a captured incoming webhook request for a project.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Project $project, array $attributes): WebhookCall
    {
        return $project->webhookCalls()->create([
            'project_id' => $project->id,
            'event' => $attributes['event'] ?? null,
            'method' => $attributes['method'] ?? 'POST',
            'url' => $attributes['url'] ?? null,
            'request_headers' => $attributes['request_headers'] ?? null,
            'request_payload' => $attributes['request_payload'] ?? null,
            'ip_address' => $attributes['ip_address'] ?? null,
            'user_agent' => $attributes['user_agent'] ?? null,
        ]);
    }
}
