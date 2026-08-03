<?php

namespace App\Actions\Metrics;

use App\Models\MetricSample;
use App\Models\Project;
use Illuminate\Support\Carbon;

class QueryRequestAnalytics
{
    /**
     * Get aggregated HTTP request metrics and analytics for a project.
     *
     * @return array<string, mixed>
     */
    public function handle(Project $project, Carbon $from, Carbon $to): array
    {
        // Query metric samples for http_requests_total
        $samples = MetricSample::query()
            ->where('project_id', $project->id)
            ->where('name', 'http_requests_total')
            ->whereBetween('recorded_at', [$from, $to])
            ->get();

        $totalRequests = 0;
        $statusCounts = ['2xx' => 0, '3xx' => 0, '4xx' => 0, '5xx' => 0];
        $methodCounts = [];

        // Time series bucket points
        $timeBuckets = [];

        foreach ($samples as $sample) {
            $val = (float) $sample->value;
            $totalRequests += $val;

            $labels = $sample->labels ?? [];
            $status = (string) ($labels['status'] ?? '200');
            $method = strtoupper((string) ($labels['method'] ?? 'GET'));

            $firstChar = substr($status, 0, 1);
            $groupKey = match ($firstChar) {
                '2' => '2xx',
                '3' => '3xx',
                '4' => '4xx',
                '5' => '5xx',
                default => '2xx',
            };
            $statusCounts[$groupKey] += $val;

            $methodCounts[$method] = ($methodCounts[$method] ?? 0) + $val;

            $timestampMs = $sample->recorded_at->timestamp * 1000;
            if (! isset($timeBuckets[$timestampMs])) {
                $timeBuckets[$timestampMs] = ['2xx' => 0, '3xx' => 0, '4xx' => 0, '5xx' => 0];
            }
            $timeBuckets[$timestampMs][$groupKey] += $val;
        }

        ksort($timeBuckets);

        $series = [
            '2xx' => [],
            '3xx' => [],
            '4xx' => [],
            '5xx' => [],
        ];

        foreach ($timeBuckets as $ts => $counts) {
            foreach (['2xx', '3xx', '4xx', '5xx'] as $key) {
                $series[$key][] = ['x' => $ts, 'y' => $counts[$key]];
            }
        }

        $successRate = $totalRequests > 0
            ? round((($statusCounts['2xx'] + $statusCounts['3xx']) / $totalRequests) * 100, 1)
            : 100.0;

        $recentRequests = $samples->sortByDesc('recorded_at')->take(20)->values()->map(function ($sample) {
            $labels = $sample->labels ?? [];

            return [
                'timestamp' => $sample->recorded_at->toDateTimeString(),
                'ip' => (string) ($labels['ip'] ?? '127.0.0.1'),
                'method' => strtoupper((string) ($labels['method'] ?? 'GET')),
                'status' => (string) ($labels['status'] ?? '200'),
                'user_agent' => (string) ($labels['user_agent'] ?? 'Browser / Agent'),
                'count' => (int) $sample->value,
            ];
        })->all();

        return [
            'total_requests' => (int) $totalRequests,
            'success_rate' => $successRate,
            'status_counts' => $statusCounts,
            'method_counts' => $methodCounts,
            'recent_requests' => $recentRequests,
            'series' => [
                ['name' => '2xx Success', 'data' => $series['2xx']],
                ['name' => '3xx Redirect', 'data' => $series['3xx']],
                ['name' => '4xx Client Error', 'data' => $series['4xx']],
                ['name' => '5xx Server Error', 'data' => $series['5xx']],
            ],
        ];
    }
}
