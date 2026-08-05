<?php

namespace App\Services\AiAssistant;

use App\Services\AiAssistant\Exceptions\AiProviderException;

interface AiProvider
{
    /**
     * Ask the provider to answer a prompt given a system context.
     *
     * @throws AiProviderException
     */
    public function ask(string $system, string $prompt): string;

    /**
     * Stream the provider's answer token-by-token.
     *
     * @return \Generator<int, string, void, void> Yields content chunks as they arrive.
     *
     * @throws AiProviderException
     */
    public function stream(string $system, string $prompt): \Generator;
}
