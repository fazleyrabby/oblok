<?php

namespace AtlasAgent;

class Config
{
    public string $baseUrl = '';

    public string $apiKey = '';

    public string $projectId = '';

    public string $agentName = 'atlas-agent';

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

        $config->baseUrl = rtrim((string) ($env['ATLAS_URL'] ?? ''), '/');
        $config->apiKey = (string) ($env['ATLAS_API_KEY'] ?? '');
        $config->projectId = (string) ($env['ATLAS_PROJECT_ID'] ?? '');
        $config->agentName = (string) ($env['ATLAS_AGENT_NAME'] ?? 'atlas-agent');

        $files = (string) ($env['ATLAS_LOG_FILES'] ?? $env['ATLAS_LOG_FILE'] ?? '');
        $config->logFiles = array_values(array_filter(array_map('trim', explode(',', $files))));

        $access = (string) ($env['ATLAS_ACCESS_LOG'] ?? '');
        $config->accessLogFile = $access !== '' ? $access : null;

        if (isset($env['ATLAS_POLL_INTERVAL'])) {
            $config->pollInterval = max(1, (int) $env['ATLAS_POLL_INTERVAL']);
        }
        if (isset($env['ATLAS_FLUSH_INTERVAL'])) {
            $config->flushInterval = max(1, (int) $env['ATLAS_FLUSH_INTERVAL']);
        }

        return $config;
    }
}
