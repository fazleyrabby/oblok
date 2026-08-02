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

            $isExpected = $statusCode === $service->expected_status_code;

            return new HealthCheckResultData(
                status: $isExpected ? 'healthy' : 'failing',
                statusCode: $statusCode,
                responseTimeMs: $durationMs,
                errorMessage: $isExpected ? null : "HTTP status code {$statusCode} returned (Expected: {$service->expected_status_code})",
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
