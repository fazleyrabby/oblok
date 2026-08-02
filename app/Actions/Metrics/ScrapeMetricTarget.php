<?php

namespace App\Actions\Metrics;

use App\Models\MetricTarget;
use App\Services\Metrics\PrometheusExpositionParser;
use Illuminate\Support\Facades\Http;

class ScrapeMetricTarget
{
    public function __construct(
        private readonly PrometheusExpositionParser $parser,
        private readonly IngestMetrics $ingest,
    ) {}

    /**
     * Fetch a Prometheus exposition endpoint and ingest its samples.
     *
     * Updates the target's scrape metadata on success or failure.
     */
    public function handle(MetricTarget $target): void
    {
        try {
            $response = Http::timeout((int) config('atlas.metrics.scrape_timeout', 10))
                ->accept('text/plain')
                ->get($target->url);

            if (! $response->successful()) {
                throw new \RuntimeException('Scrape returned HTTP '.$response->status());
            }

            $samples = $this->parser->parse($response->body());

            $this->ingest->handle($target->project, $samples);

            $target->forceFill([
                'last_scraped_at' => now(),
                'last_error' => null,
            ])->save();
        } catch (\Throwable $e) {
            $target->forceFill(['last_error' => $e->getMessage()])->save();

            throw $e;
        }
    }
}
