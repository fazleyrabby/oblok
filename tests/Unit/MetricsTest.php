<?php

use App\Actions\Metrics\IngestMetrics;
use App\Actions\Metrics\QueryMetricSeries;
use App\Actions\Metrics\QueryResourceMetrics;
use App\Actions\Metrics\ScrapeMetricTarget;
use App\Models\MetricSample;
use App\Models\MetricTarget;
use App\Models\Project;
use App\Services\Metrics\PrometheusExpositionParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('parser extracts samples with and without labels', function () {
    $body = <<<'PROM'
# HELP http_requests_total Total HTTP requests
# TYPE http_requests_total counter
http_requests_total{method="GET",code="200"} 42
http_requests_total 7
cpu_usage 0.35 1720000000000
PROM;

    $samples = app(PrometheusExpositionParser::class)->parse($body);

    expect($samples)->toHaveCount(3)
        ->and($samples[0]['name'])->toBe('http_requests_total')
        ->and($samples[0]['labels'])->toBe(['method' => 'GET', 'code' => '200'])
        ->and($samples[0]['value'])->toBe(42.0)
        ->and($samples[1]['labels'])->toBe([])
        ->and($samples[2]['value'])->toBe(0.35)
        ->and($samples[2]['recorded_at'])->not->toBeNull();
});

test('parser ignores blank lines and comments', function () {
    $samples = app(PrometheusExpositionParser::class)->parse("# TYPE x counter\n\nfoo 1\n");

    expect($samples)->toHaveCount(1)
        ->and($samples[0]['name'])->toBe('foo');
});

test('parser handles escaped label values', function () {
    $samples = app(PrometheusExpositionParser::class)->parse('http_requests_total{route="users\\"admin"} 1');

    expect($samples)->toHaveCount(1)
        ->and($samples[0]['labels']['route'])->toBe('users"admin');
});

test('ingest action persists samples with labels and timestamps', function () {
    $project = Project::factory()->create();

    $count = app(IngestMetrics::class)->handle($project, [
        ['name' => 'http_requests_total', 'value' => 42, 'labels' => ['code' => '200']],
        ['name' => 'cpu_usage', 'value' => 0.5, 'recorded_at' => now()->toIso8601String()],
    ]);

    expect($count)->toBe(2)
        ->and($project->metricSamples()->count())->toBe(2)
        ->and($project->metricSamples()->named('http_requests_total')->first()->labels)->toBe(['code' => '200']);
});

test('query action buckets samples into a single chart series', function () {
    $project = Project::factory()->create();
    $now = Carbon::parse('2026-08-02 12:00:00');

    foreach (range(20, 29) as $value) {
        MetricSample::factory()->create([
            'project_id' => $project->id,
            'name' => 'temperature',
            'labels' => ['room' => 'kitchen'],
            'value' => $value,
            'recorded_at' => $now->copy()->addMinutes($value),
        ]);
    }

    $series = app(QueryMetricSeries::class)->handle(
        $project,
        'temperature',
        $now->copy()->addMinutes(19),
        $now->copy()->addMinutes(31),
        points: 1
    );

    expect($series)->toHaveCount(1)
        ->and($series[0]['labels'])->toBe(['room' => 'kitchen'])
        ->and(count($series[0]['points']))->toBe(1)
        ->and($series[0]['points'][0][1])->toBe(24.5);

    $maxSeries = app(QueryMetricSeries::class)->handle(
        $project,
        'temperature',
        $now->copy()->addMinutes(19),
        $now->copy()->addMinutes(31),
        points: 1,
        aggregate: 'max'
    );

    expect($maxSeries[0]['points'][0][1])->toBe(29.0);
});

test('query action supports label filters', function () {
    $project = Project::factory()->create();
    MetricSample::factory()->create([
        'project_id' => $project->id,
        'name' => 'requests',
        'labels' => ['env' => 'prod'],
        'value' => 1,
        'recorded_at' => now(),
    ]);
    MetricSample::factory()->create([
        'project_id' => $project->id,
        'name' => 'requests',
        'labels' => ['env' => 'staging'],
        'value' => 9,
        'recorded_at' => now(),
    ]);

    $series = app(QueryMetricSeries::class)->handle(
        $project,
        'requests',
        now()->subMinute(),
        now()->addMinute(),
        points: 10,
        labelFilters: ['env' => 'prod']
    );

    expect($series)->toHaveCount(1)
        ->and($series[0]['labels'])->toBe(['env' => 'prod']);
});

test('query action caps high-cardinality series to keep charts responsive', function () {
    $project = Project::factory()->create();
    $now = Carbon::parse('2026-08-02 12:00:00');

    // 30 distinct label combos, each with a handful of samples.
    foreach (range(0, 29) as $bucket) {
        foreach (range(0, 4) as $n) {
            MetricSample::factory()->create([
                'project_id' => $project->id,
                'name' => 'container_memory',
                'labels' => ['type' => 'container', 'used_bytes' => (string) ($bucket * 1000 + $n)],
                'value' => $bucket + $n,
                'recorded_at' => $now->copy()->addMinutes($n),
            ]);
        }
    }

    $series = app(QueryMetricSeries::class)->handle(
        $project,
        'container_memory',
        $now->copy()->subMinute(),
        $now->copy()->addMinutes(10),
        points: 10,
        maxSeries: 20
    );

    expect($series)->toHaveCount(20);
});

test('resource dashboard down-samples dense, high-cardinality samples into one series per metric', function () {
    $project = Project::factory()->create();
    $now = Carbon::parse('2026-08-02 12:00:00');

    // 30 distinct volatile label combos (used_bytes/limit_bytes), each with 10
    // samples spread across the window — the exact shape that previously handed
    // the browser ~1400 points and froze ApexCharts.
    foreach (range(0, 29) as $bucket) {
        foreach (range(0, 9) as $n) {
            MetricSample::factory()->create([
                'project_id' => $project->id,
                'name' => 'container_memory_usage_percent',
                'labels' => ['type' => 'container', 'used_bytes' => (string) ($bucket * 1000 + $n)],
                'value' => $bucket + $n,
                'recorded_at' => $now->copy()->addMinutes($n),
            ]);
        }
    }

    $data = app(QueryResourceMetrics::class)->handle(
        $project,
        $now->copy()->subMinute(),
        $now->copy()->addMinutes(10),
        points: 60
    );

    // One series per metric (labels collapsed), each capped to the requested
    // number of points regardless of how many label combos exist.
    expect($data['series'])->toHaveCount(4)
        ->and($data['has_container_metrics'])->toBeTrue()
        ->and(count($data['series'][2]['data']))->toBeLessThanOrEqual(60);
});

test('scrape action ingests samples and updates target metadata', function () {
    Http::fake([
        'http://host:9100/metrics' => Http::response("# TYPE cpu_usage gauge\ncpu_usage 0.5\n"),
    ]);

    $project = Project::factory()->create();
    $target = MetricTarget::factory()->create([
        'project_id' => $project->id,
        'url' => 'http://host:9100/metrics',
    ]);

    app(ScrapeMetricTarget::class)->handle($target);

    expect($project->metricSamples()->count())->toBe(1)
        ->and($target->fresh()->last_scraped_at)->not->toBeNull()
        ->and($target->fresh()->last_error)->toBeNull();
});

test('scrape action records an error on failure', function () {
    Http::fake([
        'http://host:9100/metrics' => Http::response('', 500),
    ]);

    $project = Project::factory()->create();
    $target = MetricTarget::factory()->create(['project_id' => $project->id, 'url' => 'http://host:9100/metrics']);

    try {
        app(ScrapeMetricTarget::class)->handle($target);
    } catch (Throwable) {
        // expected
    }

    expect($target->fresh()->last_error)->not->toBeNull();
});
