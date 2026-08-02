<?php

namespace App\Jobs;

use App\Actions\Metrics\ScrapeMetricTarget;
use App\Models\MetricTarget;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScrapeMetricTargetJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(public string $targetId) {}

    /**
     * Execute the job.
     */
    public function handle(ScrapeMetricTarget $scrape): void
    {
        $target = MetricTarget::find($this->targetId);

        if (! $target) {
            return;
        }

        $scrape->handle($target);
    }
}
