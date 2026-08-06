<?php

namespace App\Support\Alerts\Sources;

use App\Enums\AlertMetric;
use App\Models\AlertRule;
use App\Models\HealthCheckResult;
use App\Support\Alerts\MetricReading;
use App\Support\Alerts\MetricSource;
use Illuminate\Support\Facades\DB;

class ServiceHealthMetricSource implements MetricSource
{
    /**
     * Count the most recent consecutive failing health checks for the rule's project.
     */
    public function readingFor(AlertRule $rule): ?MetricReading
    {
        $serviceIds = $rule->project->services()
            ->where('is_flapping', false)
            ->pluck('services.id');

        if ($serviceIds->isEmpty()) {
            return null;
        }

        $latestCheck = HealthCheckResult::query()
            ->whereIn('service_id', $serviceIds)
            ->latest('created_at')
            ->first();

        if (! $latestCheck) {
            return null;
        }

        $consecutiveFailures = (int) DB::table('health_check_results')
            ->whereIn('service_id', $serviceIds)
            ->where('status', '!=', 'healthy')
            ->where('created_at', '>=', $latestCheck->created_at->copy()->subHours(1))
            ->count();

        return new MetricReading(
            metric: AlertMetric::ServiceHealth,
            value: $consecutiveFailures,
            occurredAt: $latestCheck->created_at,
            context: ['service_id' => $latestCheck->service_id],
        );
    }
}
