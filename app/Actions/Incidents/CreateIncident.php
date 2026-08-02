<?php

namespace App\Actions\Incidents;

use App\Models\Incident;
use App\Models\Project;

class CreateIncident
{
    /**
     * Create a new operational incident record.
     *
     * @param  array{
     *     service_id?: string|null,
     *     title: string,
     *     description?: string|null,
     *     severity?: string
     * }  $data
     */
    public function handle(Project $project, array $data): Incident
    {
        return Incident::create([
            'project_id' => $project->id,
            'service_id' => $data['service_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'severity' => $data['severity'] ?? 'medium',
            'status' => 'investigating',
            'started_at' => now(),
        ]);
    }
}
