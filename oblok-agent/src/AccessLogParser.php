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
        // 1. Standard Nginx combined format: IP - - [date] "METHOD path HTTP/1.1" status bytes "referer" "user_agent" [request_time]
        $nginxPattern = '/^(\S+) - (\S+) \[[^\]]+\] "(\S+) ([^"]*)" (\d{3}) (\d+|-) "(?:[^"]*)" "([^"]*)"(?: (\d+\.\d+))?$/';

        if (preg_match($nginxPattern, trim($line), $matches)) {
            return [
                'ip' => $matches[1],
                'method' => $matches[3],
                'path' => $matches[4],
                'status' => (int) $matches[5],
                'user_agent' => $matches[7] !== '-' ? $matches[7] : 'Unknown',
                'request_time' => isset($matches[8]) && $matches[8] !== '' ? (float) $matches[8] : null,
            ];
        }

        // 2. Traefik access log format: IP - - [date] "METHOD path HTTP/1.1" status bytes "referer" "user_agent" count "router@docker" "http://..." duration_ms
        $traefikPattern = '/^(\S+) - (\S+) \[[^\]]+\] "(\S+) ([^"]*)" (\d{3}) (\d+|-) "(?:[^"]*)" "([^"]*)" \d+ "(?:[^"]*)" "(?:[^"]*)" (\d+)ms$/';

        if (preg_match($traefikPattern, trim($line), $matches)) {
            return [
                'ip' => $matches[1],
                'method' => $matches[3],
                'path' => $matches[4],
                'status' => (int) $matches[5],
                'user_agent' => $matches[7] !== '-' ? $matches[7] : 'Unknown',
                'request_time' => ((float) $matches[8]) / 1000.0, // Convert ms to seconds
            ];
        }

        return null;
    }
}
