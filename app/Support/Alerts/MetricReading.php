<?php

namespace App\Support\Alerts;

use App\Enums\AlertMetric;
use Illuminate\Support\Carbon;

/**
 * A point-in-time reading for a metric that an alert rule evaluates against.
 */
final readonly class MetricReading
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public AlertMetric $metric,
        public int|string $value,
        public Carbon $occurredAt,
        public array $context = [],
    ) {}
}
