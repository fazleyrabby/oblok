<?php

namespace App\Support\Alerts\Sources;

use App\Enums\AlertMetric;
use App\Models\AlertRule;
use App\Support\Alerts\MetricReading;
use App\Support\Alerts\MetricSource;

class IncidentOpenedMetricSource implements MetricSource
{
    /**
     * Report the number of open incidents in the rule's window.
     */
    public function readingFor(AlertRule $rule): ?MetricReading
    {
        $count = $rule->project->incidents()
            ->where('started_at', '>=', now()->subMinutes($rule->window_minutes))
            ->open()
            ->count();

        return new MetricReading(
            metric: AlertMetric::IncidentOpened,
            value: $count,
            occurredAt: now(),
        );
    }
}
