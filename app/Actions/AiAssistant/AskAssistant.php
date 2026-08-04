<?php

namespace App\Actions\AiAssistant;

use App\Models\Project;
use App\Services\AiAssistant\AiProviderManager;
use App\Services\AiAssistant\Exceptions\AiProviderException;

class AskAssistant
{
    public function __construct(private AiProviderManager $manager)
    {
    }

    /**
     * Answer a natural-language question about the project using its live
     * operational context.
     *
     * @throws AiProviderException
     */
    public function handle(Project $project, string $question): string
    {
        $system = implode("\n", [
            'You are the oblok operational assistant, embedded in a self-hosted developer operations platform.',
            'You answer questions about a project\'s services, incidents, deployments, alerts, and logs.',
            'Be concise, technical, and accurate. If the supplied context does not contain the answer, say so.',
            'Never invent data that is not present in the provided context.',
        ]);

        $prompt = sprintf(
            "Operational context for the \"%s\" project:\n\n%s\n\nQuestion: %s",
            $project->name,
            $this->buildContext($project),
            $question
        );

        return $this->manager->driver()->ask($system, $prompt);
    }

    /**
     * Build a compact snapshot of the project's operational state.
     */
    protected function buildContext(Project $project): string
    {
        $limit = (int) config('oblok.ai.context_limit', 12);

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
                $incident->started_at?->toIso8601String() ?? 'unknown',
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
                $alert->severity,
                $alert->subject,
                $alert->triggered_at?->toIso8601String() ?? 'unknown'
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
