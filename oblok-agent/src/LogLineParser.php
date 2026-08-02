<?php

namespace OblokAgent;

class LogLineParser
{
    /**
     * @var array<string, string>
     */
    private const LEVEL_MAP = [
        'EMERGENCY' => 'emergency',
        'ALERT' => 'alert',
        'CRITICAL' => 'critical',
        'ERROR' => 'error',
        'ERR' => 'error',
        'WARNING' => 'warning',
        'WARN' => 'warning',
        'NOTICE' => 'notice',
        'INFO' => 'info',
        'DEBUG' => 'debug',
    ];

    /**
     * Parse a log line into a message, level, context, and channel.
     *
     * @return array{message: string, level: string, context: array<int|string, mixed>|null, channel: string|null}
     */
    public function parse(string $line): array
    {
        $decoded = json_decode($line, true);

        if (is_array($decoded) && isset($decoded['message'])) {
            return [
                'message' => (string) $decoded['message'],
                'level' => $this->normalizeLevel($decoded['level'] ?? 'info'),
                'context' => isset($decoded['context']) && is_array($decoded['context']) ? $decoded['context'] : null,
                'channel' => isset($decoded['channel']) ? (string) $decoded['channel'] : null,
            ];
        }

        if (preg_match('/^\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}\][ ]?(?:[a-z0-9_.]+\.)?([A-Z]+): (.*)$/i', $line, $matches)) {
            return [
                'message' => $matches[2],
                'level' => $this->normalizeLevel($matches[1]),
                'context' => null,
                'channel' => null,
            ];
        }

        return [
            'message' => $line,
            'level' => 'info',
            'context' => null,
            'channel' => null,
        ];
    }

    /**
     * Normalize a log level to the values oblok accepts.
     */
    private function normalizeLevel(mixed $level): string
    {
        return self::LEVEL_MAP[strtoupper((string) $level)] ?? 'info';
    }
}
