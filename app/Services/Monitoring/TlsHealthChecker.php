<?php

namespace App\Services\Monitoring;

use App\Models\Service;
use App\Services\Monitoring\Contracts\HealthCheckerInterface;
use Throwable;

class TlsHealthChecker implements HealthCheckerInterface
{
    public function check(Service $service): HealthCheckResultData
    {
        $startTime = microtime(true);
        $target = $service->target;

        $parsed = parse_url($target);
        $host = $parsed['host'] ?? $parsed['path'] ?? $target;
        $port = $parsed['port'] ?? 443;

        if (str_contains($host, ':')) {
            [$hostPart, $portPart] = explode(':', $host, 2);
            $host = $hostPart;
            $port = (int) $portPart;
        }

        $config = is_array($service->config) ? $service->config : [];
        $minValidDays = (int) ($config['min_cert_days'] ?? 7);
        $timeout = max(1, $service->timeout);

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        try {
            $client = @stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                $timeout,
                STREAM_CLIENT_CONNECT,
                $context
            );

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if (! $client) {
                return new HealthCheckResultData(
                    status: 'failing',
                    statusCode: null,
                    responseTimeMs: $durationMs,
                    errorMessage: "TLS handshake failed for {$host}:{$port} ({$errstr})",
                );
            }

            $params = stream_context_get_params($client);
            fclose($client);

            $cert = $params['options']['ssl']['capture_peer_cert'] ?? null;

            if (! $cert) {
                return new HealthCheckResultData(
                    status: 'failing',
                    statusCode: null,
                    responseTimeMs: $durationMs,
                    errorMessage: "Could not retrieve SSL certificate from {$host}:{$port}",
                );
            }

            $certInfo = openssl_x509_parse($cert);
            $validToTimestamp = $certInfo['validTo_time_t'] ?? null;

            if (! $validToTimestamp) {
                return new HealthCheckResultData(
                    status: 'failing',
                    statusCode: null,
                    responseTimeMs: $durationMs,
                    errorMessage: 'Could not parse certificate validity date',
                );
            }

            $daysRemaining = (int) floor(($validToTimestamp - time()) / 86400);

            if ($daysRemaining < 0) {
                return new HealthCheckResultData(
                    status: 'failing',
                    statusCode: null,
                    responseTimeMs: $durationMs,
                    errorMessage: "SSL certificate for {$host} expired {$daysRemaining} days ago",
                );
            }

            if ($daysRemaining < $minValidDays) {
                return new HealthCheckResultData(
                    status: 'failing',
                    statusCode: null,
                    responseTimeMs: $durationMs,
                    errorMessage: "SSL certificate for {$host} expires in {$daysRemaining} days (Threshold: {$minValidDays} days)",
                );
            }

            return new HealthCheckResultData(
                status: 'healthy',
                statusCode: null,
                responseTimeMs: $durationMs,
                errorMessage: null,
            );
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            return new HealthCheckResultData(
                status: 'failing',
                statusCode: null,
                responseTimeMs: $durationMs,
                errorMessage: "TLS check error: {$e->getMessage()}",
            );
        }
    }
}
