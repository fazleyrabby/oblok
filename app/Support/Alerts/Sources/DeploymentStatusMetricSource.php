<?php

namespace App\Support\Alerts\Sources;

use App\Enums\AlertMetric;
use App\Models\AlertRule;
use App\Support\Alerts\MetricReading;
use App\Support\Alerts\MetricSource;

class DeploymentStatusMetricSource implements MetricSource
{
    /**
     * Report whether the latest deployment in the window failed (1) or not (0).
     */
    public function readingFor(AlertRule $rule): ?MetricReading
    {
        $latest = $rule->project->deployments()
            ->where('started_at', '>=', now()->subMinutes($rule->window_minutes))
            ->first();

        if (! $latest) {
            return null;
        }

        return new MetricReading(
            metric: AlertMetric::DeploymentStatus,
            value: $latest->status === 'failed' ? 1 : 0,
            occurredAt: $latest->started_at,
            context: [
                'deployment_id' => $latest->id,
                'status' => $latest->status,
            ],
        );
    }
}
