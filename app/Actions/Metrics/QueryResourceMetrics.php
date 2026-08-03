<?php

namespace App\Actions\Metrics;

use App\Models\MetricSample;
use App\Models\Project;
use Illuminate\Support\Carbon;

class QueryResourceMetrics
{
    /**
     * Get aggregated resource metrics (CPU, Memory, Disk, Network) for a project.
     *
     * @return array<string, mixed>
     */
    public function handle(Project $project, Carbon $from, Carbon $to): array
    {
        $metricNames = [
            'system_cpu_usage_percent',
            'system_memory_usage_percent',
            'container_memory_usage_percent',
            'system_disk_usage_percent',
        ];

        $samples = MetricSample::query()
            ->where('project_id', $project->id)
            ->whereIn('name', $metricNames)
            ->whereBetween('recorded_at', [$from, $to])
            ->get();

        $latestCpu = 0.0;
        $latestMem = 0.0;
        $latestContainerMem = 0.0;
        $latestDisk = 0.0;

        $seriesData = [
            'cpu' => [],
            'memory' => [],
            'container_memory' => [],
            'disk' => [],
        ];

        foreach ($samples as $sample) {
            $val = round((float) $sample->value, 2);
            $ts = $sample->recorded_at->timestamp * 1000;

            match ($sample->name) {
                'system_cpu_usage_percent' => (function () use ($val, $ts, &$latestCpu, &$seriesData) {
                    $latestCpu = $val;
                    $seriesData['cpu'][] = ['x' => $ts, 'y' => $val];
                })(),
                'system_memory_usage_percent' => (function () use ($val, $ts, &$latestMem, &$seriesData) {
                    $latestMem = $val;
                    $seriesData['memory'][] = ['x' => $ts, 'y' => $val];
                })(),
                'container_memory_usage_percent' => (function () use ($val, $ts, &$latestContainerMem, &$seriesData) {
                    $latestContainerMem = $val;
                    $seriesData['container_memory'][] = ['x' => $ts, 'y' => $val];
                })(),
                'system_disk_usage_percent' => (function () use ($val, $ts, &$latestDisk, &$seriesData) {
                    $latestDisk = $val;
                    $seriesData['disk'][] = ['x' => $ts, 'y' => $val];
                })(),
                default => null,
            };
        }

        return [
            'latest' => [
                'cpu_percent' => $latestCpu,
                'memory_percent' => $latestMem,
                'container_memory_percent' => $latestContainerMem,
                'disk_percent' => $latestDisk,
            ],
            'series' => [
                ['name' => 'Host CPU Usage %', 'data' => $seriesData['cpu']],
                ['name' => 'Host RAM Usage %', 'data' => $seriesData['memory']],
                ['name' => 'Container RAM %', 'data' => $seriesData['container_memory']],
                ['name' => 'Host Disk Usage %', 'data' => $seriesData['disk']],
            ],
        ];
    }
}
