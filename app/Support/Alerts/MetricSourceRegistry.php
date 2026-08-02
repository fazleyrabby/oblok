<?php

namespace App\Support\Alerts;

use App\Enums\AlertMetric;

class MetricSourceRegistry
{
    /**
     * @var array<AlertMetric, class-string<MetricSource>>
     */
    private array $sources = [];

    public function register(AlertMetric $metric, string $sourceClass): void
    {
        $this->sources[$metric->value] = $sourceClass;
    }

    /**
     * Resolve a metric source for the given metric.
     */
    public function for(AlertMetric $metric): MetricSource
    {
        $class = $this->sources[$metric->value] ?? null;

        if (! $class) {
            throw new \RuntimeException("No metric source registered for [{$metric->value}].");
        }

        return app($class);
    }
}
