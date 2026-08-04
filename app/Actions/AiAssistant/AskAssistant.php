<?php

namespace App\Actions\AiAssistant;

use App\Models\Project;
use App\Services\AiAssistant\AiProviderManager;
use App\Services\AiAssistant\Exceptions\AiProviderException;
use App\Services\AiAssistant\ProjectContextBuilder;

class AskAssistant
{
    public function __construct(
        private AiProviderManager $manager,
        private ProjectContextBuilder $context,
    ) {}

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
            $this->context->build($project),
            $question
        );

        return $this->manager->driver()->ask($system, $prompt);
    }
}
