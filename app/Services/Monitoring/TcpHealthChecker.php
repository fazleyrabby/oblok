<?php

namespace App\Services\Monitoring;

use App\Models\Service;
use App\Services\Monitoring\Contracts\HealthCheckerInterface;
use Throwable;

class TcpHealthChecker implements HealthCheckerInterface
{
    public function check(Service $service): HealthCheckResultData
    {
        $startTime = microtime(true);
        $target = $service->target;

        // Parse host and port (default 80/443 if unassigned)
        $parsed = parse_url($target);
        $host = $parsed['host'] ?? $parsed['path'] ?? $target;
        $port = $parsed['port'] ?? (($parsed['scheme'] ?? '') === 'https' ? 443 : 80);

        // Extract port if specified as host:port
        if (str_contains($host, ':')) {
            [$hostPart, $portPart] = explode(':', $host, 2);
            $host = $hostPart;
            $port = (int) $portPart;
        }

        $timeout = max(1, $service->timeout);

        try {
            $connection = @fsockopen($host, $port, $errorCode, $errorMessage, $timeout);

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if (is_resource($connection)) {
                fclose($connection);

                return new HealthCheckResultData(
                    status: 'healthy',
                    statusCode: null,
                    responseTimeMs: $durationMs,
                    errorMessage: null,
                );
            }

            return new HealthCheckResultData(
                status: 'failing',
                statusCode: null,
                responseTimeMs: $durationMs,
                errorMessage: "TCP connection failed to {$host}:{$port} ({$errorMessage})",
            );
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            return new HealthCheckResultData(
                status: 'failing',
                statusCode: null,
                responseTimeMs: $durationMs,
                errorMessage: "TCP connection error: {$e->getMessage()}",
            );
        }
    }
}
