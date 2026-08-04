<?php

namespace App\Services\AiAssistant;

use App\Models\Project;

class ProjectContextBuilder
{
    /**
     * Build a compact snapshot of the project's operational state for the AI
     * assistant. Reused by both the chat action and incident suggestions.
     */
    public function build(Project $project, ?int $limit = null): string
    {
        $limit = $limit ?? (int) config('oblok.ai.context_limit', 12);

        $services = $project->services()->latest()->limit($limit)->get();
        $incidents = $project->incidents()->latest()->limit($limit)->get();
        $deployments = $project->deployments()->limit($limit)->get();
        $alerts = $project->alertEvents()->latest()->limit($limit)->get();
        $logs = $project->logs()->limit($limit)->get();

        $lines = [];

        $lines[] = 'Services ('.count($services).'):';
        foreach ($services as $service) {
            $lines[] = sprintf(
                '- %s [%s] %s (last checked %s)',
                $service->name,
                $service->status,
                $service->target,
                $service->last_checked_at?->toIso8601String() ?? 'never'
            );
        }

        $lines[] = '';
        $lines[] = 'Incidents ('.count($incidents).'):';
        foreach ($incidents as $incident) {
            $lines[] = sprintf(
                '- %s [%s] %s (started %s%s)',
                $incident->title,
                $incident->severity,
                $incident->status,
                $incident->started_at->toIso8601String(),
                $incident->resolved_at ? ' resolved '.$incident->resolved_at->toIso8601String() : ''
            );
        }

        $lines[] = '';
        $lines[] = 'Recent deployments ('.count($deployments).'):';
        foreach ($deployments as $deployment) {
            $lines[] = sprintf(
                '- %s %s -> %s [%s]',
                $deployment->started_at?->toIso8601String() ?? 'unknown',
                $deployment->environment,
                $deployment->commit_message ?: ($deployment->commit_hash ?: 'n/a'),
                $deployment->status
            );
        }

        $lines[] = '';
        $lines[] = 'Recent alerts ('.count($alerts).'):';
        foreach ($alerts as $alert) {
            $lines[] = sprintf(
                '- [%s] %s (%s)',
                $alert->severity->value,
                $alert->subject,
                $alert->triggered_at->toIso8601String()
            );
        }

        $lines[] = '';
        $lines[] = 'Most recent log entries ('.count($logs).'):';
        foreach ($logs as $log) {
            $message = mb_strimwidth((string) $log->message, 0, 200, '…');
            $lines[] = sprintf('- [%s] %s', $log->level, $message);
        }

        return implode("\n", $lines);
    }
}
