<?php

namespace App\Actions\Metrics;

use App\Models\MetricSample;
use App\Models\Project;
use Illuminate\Support\Carbon;

class QueryMetricSeries
{
    /**
     * Resolve the recorded samples for a metric into down-sampled chart series.
     *
     * @param  array<string, string>  $labelFilters
     * @return array<int, array{name: string, labels: array<string, string|int|float>, points: array<int, array{0: int, 1: float}>}>
     */
    public function handle(
        Project $project,
        string $name,
        Carbon $from,
        Carbon $to,
        int $points = 60,
        string $aggregate = 'avg',
        array $labelFilters = []
    ): array {
        $samples = MetricSample::query()
            ->forProject($project->id)
            ->named($name)
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at')
            ->get(['labels', 'value', 'recorded_at']);

        $points = max(1, $points);
        $stepMs = max(1, (int) ceil(($to->getTimestampMs() - $from->getTimestampMs()) / $points));

        $series = [];

        foreach ($samples as $sample) {
            if (! $this->matchesFilters($sample->labels, $labelFilters)) {
                continue;
            }

            $key = $sample->labels === [] ? '__no_labels__' : json_encode($sample->labels);

            $index = (int) floor(($sample->recorded_at->getTimestampMs() - $from->getTimestampMs()) / $stepMs);
            $index = min(max($index, 0), $points - 1);

            $series[$key]['labels'] = $sample->labels;
            $series[$key]['values'][$index][] = $sample->value;
        }

        $result = [];

        foreach ($series as $entry) {
            $data = [];

            for ($i = 0; $i < $points; $i++) {
                $values = $entry['values'][$i] ?? [];

                if ($values === []) {
                    continue;
                }

                $data[] = [
                    $from->getTimestampMs() + ($stepMs * $i),
                    $this->aggregate($values, $aggregate),
                ];
            }

            $result[] = [
                'name' => $name,
                'labels' => $entry['labels'],
                'points' => $data,
            ];
        }

        return $result;
    }

    /**
     * Determine whether a sample's labels satisfy the requested filters.
     *
     * @param  array<string, string|int|float>  $labels
     * @param  array<string, string>  $filters
     */
    protected function matchesFilters(array $labels, array $filters): bool
    {
        foreach ($filters as $key => $value) {
            if (! array_key_exists($key, $labels) || (string) $labels[$key] !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Collapse a set of values into a single aggregate.
     *
     * @param  array<int, float>  $values
     */
    protected function aggregate(array $values, string $method): float
    {
        return match ($method) {
            'sum' => (float) array_sum($values),
            'min' => (float) min($values),
            'max' => (float) max($values),
            'last' => (float) end($values),
            default => (float) (array_sum($values) / count($values)),
        };
    }
}
