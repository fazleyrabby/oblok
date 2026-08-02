<?php

namespace App\Actions\Services;

use App\Events\ServiceStatusChanged;
use App\Models\HealthCheckResult;
use App\Models\Service;
use App\Services\Monitoring\HttpHealthChecker;

class PingServiceHealth
{
    public function __construct(
        protected HttpHealthChecker $healthChecker,
    ) {}

    /**
     * Perform a health check probe on a service, record result, and update status.
     */
    public function handle(Service $service): HealthCheckResult
    {
        $previousStatus = $service->status;
        $resultData = $this->healthChecker->check($service);

        $result = HealthCheckResult::create([
            'service_id' => $service->id,
            'status' => $resultData->status,
            'status_code' => $resultData->statusCode,
            'response_time_ms' => $resultData->responseTimeMs,
            'error_message' => $resultData->errorMessage,
            'created_at' => now(),
        ]);

        $service->update([
            'status' => $resultData->status,
            'last_checked_at' => now(),
        ]);

        if ($previousStatus !== 'unknown' && $previousStatus !== $resultData->status) {
            event(new ServiceStatusChanged($service, $previousStatus, $resultData->status));
        }

        return $result;
    }
}
