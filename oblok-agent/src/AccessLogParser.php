<?php

namespace OblokAgent;

class AccessLogParser
{
    /**
     * Parse an nginx combined-format access log line, optionally with $request_time.
     *
     * @return array{method: string, path: string, status: int, request_time: float|null}|null
     */
    public function parse(string $line): ?array
    {
        // 1. Standard Nginx combined format with optional request_time float in seconds
        $nginxPattern = '/^(\S+) - (\S+) \[[^\]]+\] "(\S+) ([^"]*)" (\d{3}) (\d+|-) "(?:[^"]*)" "(?:[^"]*)"(?: (\d+\.\d+))?$/';

        if (preg_match($nginxPattern, trim($line), $matches)) {
            return [
                'method' => $matches[3],
                'path' => $matches[4],
                'status' => (int) $matches[5],
                'request_time' => isset($matches[7]) && $matches[7] !== '' ? (float) $matches[7] : null,
            ];
        }

        // 2. Traefik access log format: IP - - [date] "METHOD path HTTP/1.1" status bytes "-" "-" count "router@docker" "http://..." duration_ms
        $traefikPattern = '/^(\S+) - (\S+) \[[^\]]+\] "(\S+) ([^"]*)" (\d{3}) (\d+|-) "(?:[^"]*)" "(?:[^"]*)" \d+ "(?:[^"]*)" "(?:[^"]*)" (\d+)ms$/';

        if (preg_match($traefikPattern, trim($line), $matches)) {
            return [
                'method' => $matches[3],
                'path' => $matches[4],
                'status' => (int) $matches[5],
                'request_time' => ((float) $matches[7]) / 1000.0, // Convert ms to seconds
            ];
        }

        return null;
    }
}
