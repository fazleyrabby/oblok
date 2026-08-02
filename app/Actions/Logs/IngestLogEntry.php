<?php

namespace App\Actions\Logs;

use App\Models\LogEntry;
use App\Models\Project;

class IngestLogEntry
{
    /**
     * Ingest and persist a new log entry for a project.
     *
     * @param  array{
     *     level?: string,
     *     message: string,
     *     context?: array<string, mixed>,
     *     channel?: string
     * }  $data
     */
    public function handle(Project $project, array $data): LogEntry
    {
        return LogEntry::create([
            'project_id' => $project->id,
            'level' => strtolower($data['level'] ?? 'info'),
            'message' => $data['message'],
            'context' => $data['context'] ?? null,
            'channel' => $data['channel'] ?? 'production',
            'created_at' => now(),
        ]);
    }
}
