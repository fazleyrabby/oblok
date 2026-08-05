<?php

namespace App\Services\AiAssistant;

use App\Models\Conversation;
use App\Services\AiAssistant\Drivers\OpenAiCompatibleDriver;
use RuntimeException;

class AiProviderManager
{
    /**
     * Resolve the AI provider configured for the application.
     *
     * @throws RuntimeException
     */
    public function driver(): AiProvider
    {
        return match (config('oblok.ai.provider')) {
            'openai-compatible' => app(OpenAiCompatibleDriver::class),
            default => throw new RuntimeException(
                'No AI provider driver configured for ['.config('oblok.ai.provider').'].'
            ),
        };
    }

    /**
     * Resolve a dynamic driver for a specific provider.
     */
    public function forProvider(string $endpoint, string $model, ?string $key = null, int $timeout = 60): AiProvider
    {
        return new OpenAiCompatibleDriver($endpoint, $model, $key, $timeout);
    }

    /**
     * Resolve the driver for a given conversation.
     */
    public function resolveDriver(?Conversation $conversation): AiProvider
    {
        if ($conversation && $conversation->selected_provider_id && $conversation->selected_model) {
            $provider = $conversation->selectedProvider;
            if ($provider) {
                return $this->forProvider(
                    $provider->endpoint,
                    $conversation->selected_model,
                    $provider->api_key,
                    $provider->timeout ?? 60
                );
            }
        }

        return $this->driver();
    }
}
