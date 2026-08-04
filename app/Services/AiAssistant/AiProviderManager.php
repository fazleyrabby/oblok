<?php

namespace App\Services\AiAssistant;

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
}
