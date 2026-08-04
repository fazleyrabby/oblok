<?php

namespace App\Services\AiAssistant\Drivers;

use App\Services\AiAssistant\AiProvider;
use App\Services\AiAssistant\Exceptions\AiProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class OpenAiCompatibleDriver implements AiProvider
{
    /**
     * Ask the configured OpenAI-compatible chat completions endpoint.
     *
     * @throws AiProviderException
     */
    public function ask(string $system, string $prompt): string
    {
        try {
            $response = $this->client()->post('/chat/completions', [
                'model' => (string) config('oblok.ai.model'),
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'max_tokens' => 1200,
            ]);

            $response->throw();

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (! is_string($content) || trim($content) === '') {
                throw new AiProviderException('The AI provider returned an empty response.');
            }

            return trim($content);
        } catch (ConnectionException|RequestException $e) {
            throw new AiProviderException('AI provider request failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Build an HTTP client pointed at the configured endpoint.
     */
    protected function client(): PendingRequest
    {
        $client = Http::baseUrl((string) config('oblok.ai.endpoint'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('oblok.ai.timeout', 60));

        $key = (string) config('oblok.ai.key');

        if ($key !== '') {
            $client->withToken($key);
        }

        return $client;
    }
}
