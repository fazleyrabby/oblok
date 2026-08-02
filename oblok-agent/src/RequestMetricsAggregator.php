<?php

namespace OblokAgent;

class RequestMetricsAggregator
{
    /**
     * @var array<string, array{count: int, latency_sum: float, latency_n: int}>
     */
    private array $buckets = [];

    /**
     * Record a parsed request into its method/status bucket.
     *
     * @param  array{method: string, path: string, status: int, request_time: float|null}  $request
     */
    public function add(array $request): void
    {
        $key = $request['method'].'|'.$request['status'];

        if (! isset($this->buckets[$key])) {
            $this->buckets[$key] = ['count' => 0, 'latency_sum' => 0.0, 'latency_n' => 0];
        }

        $this->buckets[$key]['count']++;

        if ($request['request_time'] !== null) {
            $this->buckets[$key]['latency_sum'] += $request['request_time'];
            $this->buckets[$key]['latency_n']++;
        }
    }

    /**
     * Emit the accumulated samples as metric payloads and reset the buckets.
     *
     * @return array<int, array<string, mixed>>
     */
    public function flush(string $metricName, string $timestamp): array
    {
        $metrics = [];

        foreach ($this->buckets as $key => $data) {
            [$method, $status] = explode('|', $key, 2);

            $metrics[] = [
                'name' => $metricName.'_total',
                'value' => $data['count'],
                'labels' => ['method' => $method, 'status' => $status],
                'timestamp' => $timestamp,
            ];

            if ($data['latency_n'] > 0) {
                $metrics[] = [
                    'name' => $metricName.'_duration_seconds',
                    'value' => round($data['latency_sum'] / $data['latency_n'], 6),
                    'labels' => ['method' => $method, 'status' => $status],
                    'timestamp' => $timestamp,
                ];
            }
        }

        $this->buckets = [];

        return $metrics;
    }
}
