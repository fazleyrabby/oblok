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
    protected string $endpoint;
    protected string $model;
    protected ?string $key;
    protected int $timeout;

    public function __construct(
        ?string $endpoint = null,
        ?string $model = null,
        ?string $key = null,
        ?int $timeout = null
    ) {
        $this->endpoint = $endpoint ?? (string) config('oblok.ai.endpoint');
        $this->model = $model ?? (string) config('oblok.ai.model');
        $this->key = $key ?? (string) config('oblok.ai.key');
        $this->timeout = $timeout ?? (int) config('oblok.ai.timeout', 60);
    }

    /**
     * Ask the configured OpenAI-compatible chat completions endpoint.
     *
     * @throws AiProviderException
     */
    public function ask(string $system, string $prompt): string
    {
        try {
            $response = $this->client()->post('/chat/completions', [
                'model' => $this->model,
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

            return $this->sanitize($content);
        } catch (ConnectionException|RequestException $e) {
            throw new AiProviderException('AI provider request failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Reject degenerate model output (unknown tokens, repetitive loops) so it is
     * never surfaced to the user. Free-tier providers occasionally emit garbage.
     *
     * @throws AiProviderException
     */
    protected function sanitize(string $content): string
    {
        if (stripos($content, '<unk>') !== false) {
            throw new AiProviderException('The AI provider returned unreadable output.');
        }

        $words = preg_split('/\s+/', trim($content)) ?: [];
        $unique = count(array_unique($words));
        $total = count($words);

        // Degenerate loop: very few unique tokens across a long reply.
        if ($total >= 30 && $unique <= 5) {
            throw new AiProviderException('The AI provider returned repetitive output.');
        }

        return trim($content);
    }

    /**
     * Stream the provider's answer token-by-token by requesting a streaming
     * chat completion and parsing the Server-Sent Events the endpoint returns.
     *
     * @return \Generator<int, string, void, void>
     *
     * @throws AiProviderException
     */
    public function stream(string $system, string $prompt): \Generator
    {
        try {
            $response = $this->client()
                ->withOptions(['stream' => true])
                ->post('/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 1200,
                    'stream' => true,
                ]);

            $response->throw();

            $body = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (! $body->eof()) {
                $buffer .= $body->read(1024);

                while (($newline = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $newline));
                    $buffer = substr($buffer, $newline + 1);

                    $chunk = $this->parseChunk($line);

                    if ($chunk === null) {
                        continue;
                    }

                    if ($chunk === self::DONE) {
                        return;
                    }

                    if ($chunk !== '') {
                        yield $chunk;
                    }
                }
            }

            // A final data line without a trailing newline (rare but legal).
            if (trim($buffer) !== '') {
                $chunk = $this->parseChunk(trim($buffer));

                if (is_string($chunk) && $chunk !== self::DONE && $chunk !== '') {
                    yield $chunk;
                }
            }
        } catch (ConnectionException|RequestException $e) {
            throw new AiProviderException('AI provider request failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Sentinel marking the end of a streaming completion.
     */
    private const DONE = '[DONE]';

    /**
     * Extract the content delta from a single SSE line, or return null when the
     * line carries no content.
     */
    protected function parseChunk(string $line): ?string
    {
        if (! str_starts_with($line, 'data:')) {
            return null;
        }

        $data = trim(substr($line, strlen('data:')));

        if ($data === self::DONE) {
            return self::DONE;
        }

        $json = json_decode($data, true);

        if (! is_array($json)) {
            return null;
        }

        $content = $json['choices'][0]['delta']['content'] ?? null;

        return is_string($content) ? $content : '';
    }

    /**
     * Build an HTTP client pointed at the configured endpoint.
     */
    protected function client(): PendingRequest
    {
        $client = Http::baseUrl($this->endpoint)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout);

        if ($this->key !== null && $this->key !== '') {
            $client->withToken($this->key);
        }

        return $client;
    }
}
