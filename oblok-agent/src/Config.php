<?php

namespace OblokAgent;

class Config
{
    public string $baseUrl = '';

    public string $apiKey = '';

    public string $projectId = '';

    public string $agentName = 'oblok-agent';

    /** @var array<int, string> */
    public array $logFiles = [];

    public ?string $accessLogFile = null;

    public int $pollInterval = 2;

    public int $flushInterval = 10;

    /**
     * Build a configuration from environment values.
     *
     * @param  array<string, mixed>  $env
     */
    public static function fromEnv(array $env): self
    {
        $config = new self;

        $config->baseUrl = rtrim((string) ($env['OBLOK_URL'] ?? ''), '/');
        $config->apiKey = (string) ($env['OBLOK_API_KEY'] ?? '');
        $config->projectId = (string) ($env['OBLOK_PROJECT_ID'] ?? '');
        $config->agentName = (string) ($env['OBLOK_AGENT_NAME'] ?? 'oblok-agent');

        $files = (string) ($env['OBLOK_LOG_FILES'] ?? $env['OBLOK_LOG_FILE'] ?? '');
        $config->logFiles = array_values(array_filter(array_map('trim', explode(',', $files))));

        $access = (string) ($env['OBLOK_ACCESS_LOG'] ?? '');
        $config->accessLogFile = $access !== '' ? $access : null;

        if (isset($env['OBLOK_POLL_INTERVAL'])) {
            $config->pollInterval = max(1, (int) $env['OBLOK_POLL_INTERVAL']);
        }
        if (isset($env['OBLOK_FLUSH_INTERVAL'])) {
            $config->flushInterval = max(1, (int) $env['OBLOK_FLUSH_INTERVAL']);
        }

        return $config;
    }

    /**
     * Expand the configured log file patterns into concrete file paths.
     *
     * Patterns containing glob characters are expanded; literal paths pass
     * through untouched (even when the file does not exist yet).
     *
     * @return array<int, string>
     */
    public function resolveLogFiles(): array
    {
        $files = [];

        foreach ($this->logFiles as $pattern) {
            if (strpbrk($pattern, '*?[') !== false) {
                foreach (glob($pattern) ?: [] as $match) {
                    $files[] = $match;
                }

                continue;
            }

            $files[] = $pattern;
        }

        return $files;
    }
}
