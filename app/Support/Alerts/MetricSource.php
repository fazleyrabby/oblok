<?php

namespace App\Support\Alerts;

use App\Models\AlertRule;

interface MetricSource
{
    /**
     * Produce a reading for the given rule, or null when no data is available.
     */
    public function readingFor(AlertRule $rule): ?MetricReading;
}
