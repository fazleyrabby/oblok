<?php

namespace App\Actions\Metrics;

use App\Models\MetricSample;
use App\Models\Project;
use Illuminate\Support\Carbon;

class QueryResourceMetrics
{
    /**
     * Get aggregated resource metrics (CPU, Memory, Disk) for a project.
     *
     * Unlike QueryMetricSeries (which preserves per-label series), this action
     * collapses each metric into a single down-sampled chart series. This is
     * intentional: the resource dashboard plots all four metrics on one shared
     * axis, so per-label granularity is noise, and without down-sampling a busy
     * agent (e.g. container memory posted every ~11s for weeks) would hand the
     * browser hundreds of thousands of raw points — freezing ApexCharts and
     * making the page unresponsive.
     *
     * @param  int  $points  Maximum number of data points per series.
     * @return array<string, mixed>
     */
    public function handle(Project $project, Carbon $from, Carbon $to, int $points = 60): array
    {
        $metricNames = [
            'system_cpu_usage_percent',
            'system_memory_usage_percent',
            'container_memory_usage_percent',
            'system_disk_usage_percent',
        ];

        $pointCount = max(1, $points);
        $spanMs = (int) $to->getTimestampMs() - (int) $from->getTimestampMs();
        $stepMs = $spanMs > 0 ? max(1, (int) ceil($spanMs / $pointCount)) : 1;

        $raw = MetricSample::query()
            ->forProject($project->id)
            ->whereIn('name', $metricNames)
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at')
            ->get(['name', 'labels', 'value', 'recorded_at']);

        // Bucket each metric into a single series (labels collapsed) to avoid
        // high-cardinality label combos multiplying the series count.
        $buckets = [];
        $environment = 'host';

        foreach ($raw as $sample) {
            $labels = $sample->labels ?? [];

            if (isset($labels['environment']) && in_array($labels['environment'], ['host', 'container'], true)) {
                $environment = $labels['environment'];
            }

            $index = (int) floor((float) ($sample->recorded_at->getTimestampMs() - $from->getTimestampMs()) / $stepMs);
            $index = min(max($index, 0), $pointCount - 1);

            $buckets[$sample->name][$index][] = (float) $sample->value;
        }

        $buildSeries = function (string $metric) use ($from, $stepMs, $pointCount, $buckets): array {
            $data = [];
            $samples = $buckets[$metric] ?? [];

            for ($i = 0; $i < $pointCount; $i++) {
                $values = $samples[$i] ?? [];
                if ($values === []) {
                    continue;
                }

                $data[] = [
                    $from->getTimestampMs() + ($stepMs * $i),
                    round(array_sum($values) / count($values), 2),
                ];
            }

            return ['name' => $metric, 'data' => $data];
        };

        $series = [
            $buildSeries('system_cpu_usage_percent'),
            $buildSeries('system_memory_usage_percent'),
            $buildSeries('container_memory_usage_percent'),
            $buildSeries('system_disk_usage_percent'),
        ];

        $latestCpuCores = (float) (MetricSample::query()
            ->forProject($project->id)
            ->named('system_cpu_cores')
            ->whereBetween('recorded_at', [$from, $to])
            ->latest('recorded_at')
            ->value('value') ?: 1);

        $latest = [];
        $stats = [];
        $names = ['cpu', 'memory', 'container_memory', 'disk'];
        $metrics = ['system_cpu_usage_percent', 'system_memory_usage_percent', 'container_memory_usage_percent', 'system_disk_usage_percent'];

        foreach ($metrics as $i => $metric) {
            $entry = $series[$i]['data'];
            $vals = array_column($entry, 1);
            $latest[$names[$i]] = empty($vals) ? 0.0 : (float) end($vals);
            $stats[$names[$i]] = [
                'avg' => empty($vals) ? 0.0 : round(array_sum($vals) / count($vals), 1),
                'peak' => empty($vals) ? 0.0 : round(max($vals), 1),
            ];
        }

        return [
            'environment' => $environment,
            'has_container_metrics' => $series[2]['data'] !== [],
            'latest' => [
                'cpu_percent' => $latest['cpu'],
                'cpu_cores' => $latestCpuCores ?: 1,
                'memory_percent' => $latest['memory'],
                'container_memory_percent' => $latest['container_memory'],
                'disk_percent' => $latest['disk'],
            ],
            'stats' => [
                'cpu' => $stats['cpu'],
                'memory' => $stats['memory'],
                'container_memory' => $stats['container_memory'],
                'disk' => $stats['disk'],
            ],
            'series' => [
                ['name' => 'Host CPU Usage %', 'data' => $series[0]['data']],
                ['name' => 'Host RAM Usage %', 'data' => $series[1]['data']],
                ['name' => 'Container RAM %', 'data' => $series[2]['data']],
                ['name' => 'Host Disk Usage %', 'data' => $series[3]['data']],
            ],
        ];
    }
}
