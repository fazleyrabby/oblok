<?php

namespace App\Actions\Runbooks;

use App\Models\Project;
use App\Models\Runbook;

class CreateRunbook
{
    /**
     * Create a new operational runbook.
     *
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     type: string,
     *     config?: array<string, mixed>|null,
     *     trigger_type?: string,
     *     enabled?: bool,
     *     cooldown_minutes?: int,
     *     timeout_seconds?: int
     * }  $data
     */
    public function handle(Project $project, array $data): Runbook
    {
        return $project->runbooks()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'config' => $data['config'] ?? [],
            'trigger_type' => $data['trigger_type'] ?? 'both',
            'enabled' => $data['enabled'] ?? true,
            'cooldown_minutes' => $data['cooldown_minutes'] ?? 10,
            'timeout_seconds' => $data['timeout_seconds'] ?? 30,
        ]);
    }
}
