<?php

namespace App\Actions\Services;

use App\Events\ServiceHealthChanged;
use App\Events\ServiceStatusChanged;
use App\Models\HealthCheckResult;
use App\Models\Service;
use App\Services\Monitoring\HealthCheckerRegistry;

class PingServiceHealth
{
    public function __construct(
        protected HealthCheckerRegistry $healthCheckerRegistry,
    ) {}

    /**
     * Perform a health check probe on a service, record result, and update status.
     */
    public function handle(Service $service): HealthCheckResult
    {
        $previousStatus = $service->status;
        $previousFlapping = (bool) $service->is_flapping;

        $resultData = $this->healthCheckerRegistry->check($service);

        $result = HealthCheckResult::create([
            'service_id' => $service->id,
            'status' => $resultData->status,
            'status_code' => $resultData->statusCode,
            'response_time_ms' => $resultData->responseTimeMs,
            'error_message' => $resultData->errorMessage,
            'created_at' => now(),
        ]);

        $transitions = $service->calculateTransitions(10);
        $isFlapping = $previousFlapping;

        if ($previousFlapping) {
            if ($transitions < 2) {
                $isFlapping = false;
            }
        } else {
            if ($transitions >= 3) {
                $isFlapping = true;
            }
        }

        $service->update([
            'status' => $resultData->status,
            'last_checked_at' => now(),
            'is_flapping' => $isFlapping,
        ]);

        if ($previousFlapping && ! $isFlapping) {
            // Exited flapping state: force status evaluation
            event(new ServiceStatusChanged($service, 'unknown', $resultData->status));
            ServiceHealthChanged::dispatch($service->project, $service, $resultData->status);
        } elseif (! $isFlapping && $previousStatus !== 'unknown' && $previousStatus !== $resultData->status) {
            event(new ServiceStatusChanged($service, $previousStatus, $resultData->status));
            ServiceHealthChanged::dispatch($service->project, $service, $resultData->status);
        }

        return $result;
    }
}
