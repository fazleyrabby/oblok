<?php

namespace OblokAgent;

class ApiClient
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private string $projectId,
    ) {}

    /**
     * Push a single log entry to oblok.
     */
    public function pushLog(string $message, string $level, ?array $context = null, ?string $channel = null): bool
    {
        $payload = ['message' => $message, 'level' => $level];

        if ($context !== null && $context !== []) {
            $payload['context'] = $context;
        }
        if ($channel !== null && $channel !== '') {
            $payload['channel'] = $channel;
        }

        return $this->post("/api/v1/projects/{$this->projectId}/logs", $payload);
    }

    /**
     * Push a batch of metric samples to oblok.
     *
     * @param  array<int, array<string, mixed>>  $metrics
     */
    public function pushMetrics(array $metrics): bool
    {
        return $this->post("/api/v1/projects/{$this->projectId}/metrics", ['metrics' => $metrics]);
    }

    /**
     * Perform an authenticated JSON POST and report success.
     *
     * @param  array<string, mixed>  $payload
     */
    private function post(string $path, array $payload): bool
    {
        $ch = curl_init($this->baseUrl.$path);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$this->apiKey,
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status >= 200 && $status < 300;
    }
}
