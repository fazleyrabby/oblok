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
}
