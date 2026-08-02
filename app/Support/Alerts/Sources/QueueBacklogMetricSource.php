<?php

namespace App\Support\Alerts\Sources;

use App\Enums\AlertMetric;
use App\Models\AlertRule;
use App\Support\Alerts\MetricReading;
use App\Support\Alerts\MetricSource;
use Illuminate\Support\Facades\DB;

class QueueBacklogMetricSource implements MetricSource
{
    /**
     * Report the number of pending + failed jobs for the rule's project window.
     */
    public function readingFor(AlertRule $rule): ?MetricReading
    {
        $pendingJobs = (int) DB::table('jobs')->count();
        $failedJobs = (int) DB::table('failed_jobs')->count();

        return new MetricReading(
            metric: AlertMetric::QueueBacklog,
            value: $pendingJobs + $failedJobs,
            occurredAt: now(),
            context: ['pending_jobs' => $pendingJobs, 'failed_jobs' => $failedJobs],
        );
    }
}
