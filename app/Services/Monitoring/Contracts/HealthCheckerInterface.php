<?php

namespace App\Services\Monitoring\Contracts;

use App\Models\Service;
use App\Services\Monitoring\HealthCheckResultData;

interface HealthCheckerInterface
{
    /**
     * Perform a health check probe against the service target.
     */
    public function check(Service $service): HealthCheckResultData;
}
