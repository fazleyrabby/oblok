<?php

namespace AtlasAgent;

class AccessLogParser
{
    /**
     * Parse an nginx combined-format access log line, optionally with $request_time.
     *
     * @return array{method: string, path: string, status: int, request_time: float|null}|null
     */
    public function parse(string $line): ?array
    {
        $pattern = '/^(\S+) - (\S+) \[[^\]]+\] "(\S+) ([^"]*)" (\d{3}) (\d+|-) "(?:[^"]*)" "(?:[^"]*)"(?: (\d+\.\d+))?$/';

        if (! preg_match($pattern, trim($line), $matches)) {
            return null;
        }

        return [
            'method' => $matches[3],
            'path' => $matches[4],
            'status' => (int) $matches[5],
            'request_time' => isset($matches[7]) && $matches[7] !== '' ? (float) $matches[7] : null,
        ];
    }
}
