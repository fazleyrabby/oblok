<?php

namespace App\Actions\AiAssistant;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\User;
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
     * operational context, persisting the exchange to chat history.
     *
     * @throws AiProviderException
     */
    public function handle(Project $project, User $user, string $question): string
    {
        $conversation = $this->conversationFor($project, $user, $question);
        $conversation->messages()->create(['role' => 'user', 'content' => $question]);

        $driver = $this->manager->resolveDriver($conversation);

        $answer = $driver->ask(
            $this->systemPrompt(),
            $this->prompt($project, $question)
        );

        $conversation->messages()->create(['role' => 'assistant', 'content' => $answer]);

        return $answer;
    }

    /**
     * Stream an answer token-by-token while persisting the exchange.
     *
     * The user message is saved before streaming begins; the assistant message
     * is saved once the provider has finished. Yielded chunks can be forwarded
     * to the client as Server-Sent Events.
     *
     * @return \Generator<int, string, void, void>
     *
     * @throws AiProviderException
     */
    public function stream(Project $project, User $user, string $question): \Generator
    {
        $conversation = $this->conversationFor($project, $user, $question);
        $conversation->messages()->create(['role' => 'user', 'content' => $question]);

        $chunks = [];
        $driver = $this->manager->resolveDriver($conversation);

        foreach ($driver->stream(
            $this->systemPrompt(),
            $this->prompt($project, $question)
        ) as $chunk) {
            $chunks[] = $chunk;
            yield $chunk;
        }

        $answer = implode('', $chunks);

        if (trim($answer) === '') {
            throw new AiProviderException('The AI provider returned an empty response.');
        }

        $conversation->messages()->create(['role' => 'assistant', 'content' => $answer]);
    }

    /**
     * Delete the chat history for the given project and user.
     */
    public function clear(Project $project, User $user): void
    {
        $conversation = $this->conversationFor($project, $user);

        $conversation->messages()->delete();
    }

    /**
     * Resolve the single conversation held for a project and user, creating it
     * (with a title derived from the opening question) when none exists.
     */
    protected function conversationFor(Project $project, User $user, ?string $firstQuestion = null): Conversation
    {
        return $project->conversations()->firstOrCreate(
            ['user_id' => $user->id],
            ['title' => $firstQuestion !== null ? mb_strimwidth($firstQuestion, 0, 60) : null]
        );
    }

    /**
     * The shared system prompt grounding every assistant reply.
     */
    protected function systemPrompt(): string
    {
        return implode("\n", [
            'You are the oblok operational assistant, embedded in a self-hosted developer operations platform.',
            'You answer questions about a project\'s services, incidents, deployments, alerts, and logs.',
            'Be concise, technical, and accurate. If the supplied context does not contain the answer, say so.',
            'Never invent data that is not present in the provided context.',
        ]);
    }

    /**
     * Build the full user prompt from the project's live context.
     */
    protected function prompt(Project $project, string $question): string
    {
        return sprintf(
            "Operational context for the \"%s\" project:\n\n%s\n\nQuestion: %s",
            $project->name,
            $this->context->build($project),
            $question
        );
    }
}
