<?php

namespace App\Services\Monitoring;

use App\Models\Service;
use App\Services\Monitoring\Contracts\HealthCheckerInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class HttpHealthChecker implements HealthCheckerInterface
{
    public function check(Service $service): HealthCheckResultData
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($service->timeout)
                ->withoutVerifying()
                ->get($service->target);

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->status();

            $isExpectedStatus = $statusCode === $service->expected_status_code;
            $errorMessage = null;

            if (! $isExpectedStatus) {
                $errorMessage = "HTTP status code {$statusCode} returned (Expected: {$service->expected_status_code})";
            } else {
                $config = is_array($service->config) ? $service->config : [];

                // Check body assertions if configured
                $expectedBodyPattern = isset($config['expected_body_pattern']) ? (string) $config['expected_body_pattern'] : null;
                if ($expectedBodyPattern !== null && $expectedBodyPattern !== '') {
                    $body = $response->body();
                    if (@preg_match($expectedBodyPattern, '') !== false) {
                        if (! preg_match($expectedBodyPattern, $body)) {
                            $isExpectedStatus = false;
                            $errorMessage = "HTTP response body did not match regex pattern '{$expectedBodyPattern}'";
                        }
                    } elseif (! str_contains($body, $expectedBodyPattern)) {
                        $isExpectedStatus = false;
                        $errorMessage = "HTTP response body did not contain expected substring '{$expectedBodyPattern}'";
                    }
                }

                // Check header assertions if configured
                $expectedHeaders = $config['expected_headers'] ?? null;
                if ($isExpectedStatus && is_array($expectedHeaders)) {
                    foreach ($expectedHeaders as $headerName => $expectedValue) {
                        $headerVal = (string) $response->header((string) $headerName);
                        if (! str_contains($headerVal, (string) $expectedValue)) {
                            $isExpectedStatus = false;
                            $errorMessage = "HTTP response header '{$headerName}' missing or did not match expected '{$expectedValue}'";
                            break;
                        }
                    }
                }
            }

            return new HealthCheckResultData(
                status: $isExpectedStatus ? 'healthy' : 'failing',
                statusCode: $statusCode,
                responseTimeMs: $durationMs,
                errorMessage: $errorMessage,
            );
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            return new HealthCheckResultData(
                status: 'failing',
                statusCode: null,
                responseTimeMs: $durationMs,
                errorMessage: $e->getMessage(),
            );
        }
    }
}
