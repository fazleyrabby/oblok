<?php

namespace App\Jobs;

use App\Models\MetricTarget;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScrapeAllMetricTargetsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Dispatch a scrape job for every enabled metric target.
     */
    public function handle(): void
    {
        MetricTarget::query()
            ->enabled()
            ->pluck('id')
            ->each(fn (string $id) => ScrapeMetricTargetJob::dispatch($id));
    }
}
