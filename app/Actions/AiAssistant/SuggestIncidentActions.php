<?php

namespace App\Actions\AiAssistant;

use App\Models\Incident;
use App\Models\Project;
use App\Services\AiAssistant\AiProviderManager;
use App\Services\AiAssistant\Exceptions\AiProviderException;
use App\Services\AiAssistant\ProjectContextBuilder;

class SuggestIncidentActions
{
    public function __construct(
        private AiProviderManager $manager,
        private ProjectContextBuilder $context,
    ) {}

    /**
     * Generate an AI root-cause hypothesis and concrete next steps for an active
     * incident, grounded in the project's live operational context.
     *
     * @throws AiProviderException
     */
    public function handle(Project $project, Incident $incident): string
    {
        $system = implode("\n", [
            'You are the oblok operational assistant, embedded in a self-hosted developer operations platform.',
            'You analyze active incidents using the provided project operational context.',
            'Respond in two clearly labeled sections:',
            '1) "Root cause hypothesis" — a concise, plausible hypothesis based only on the supplied context.',
            '2) "Suggested next steps" — a short bulleted list of concrete actions an on-call engineer can take.',
            'Be concise, technical, and accurate. Never invent data that is not present in the provided context.',
        ]);

        $prompt = sprintf(
            "Incident details:\n"
            ."Title: %s\nSeverity: %s\nStatus: %s\nDescription: %s\nService: %s\n\n"
            ."Project operational context:\n\n%s\n\n"
            .'Based on the above, provide a root-cause hypothesis and suggested next steps.',
            $incident->title,
            $incident->severity,
            $incident->status,
            $incident->description ?? 'n/a',
            $incident->service_id ? $incident->service->name : 'n/a',
            $this->context->build($project)
        );

        return $this->manager->driver()->ask($system, $prompt);
    }
}
