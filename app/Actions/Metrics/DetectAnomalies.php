<?php

namespace App\Actions\Metrics;

use App\Models\MetricSample;
use App\Models\Project;
use App\Services\Metrics\AnomalyDetector;
use Illuminate\Support\Carbon;

class DetectAnomalies
{
    public function __construct(
        private AnomalyDetector $detector,
    ) {}

    /**
     * Detect unusual metric patterns for a project over a lookback window.
     *
     * Samples are grouped by metric name and label set, then each group's
     * series is compared against its own baseline. Results are ordered by
     * severity (largest absolute z-score first).
     *
     * @return array<int, array{name: string, labels: array<string, string|int|float>, baseline_mean: float, current_mean: float, z_score: float, percent_change: float|null, direction: string, severity: string, sample_count: int}>
     */
    public function handle(Project $project, ?Carbon $from = null, ?float $zThreshold = null, ?int $minSamples = null): array
    {
        $from ??= now()->subHours((int) config('oblok.anomaly.window_hours', 24));
        $zThreshold ??= (float) config('oblok.anomaly.z_threshold', 3.0);
        $minSamples ??= (int) config('oblok.anomaly.min_samples', 12);

        $samples = MetricSample::query()
            ->forProject($project->id)
            ->where('recorded_at', '>=', $from)
            ->orderBy('recorded_at')
            ->get(['name', 'labels', 'value', 'recorded_at']);

        $groups = [];

        foreach ($samples as $sample) {
            $key = $sample->name.'|'.($sample->labels === [] ? '__none__' : json_encode($sample->labels));

            $groups[$key]['name'] = $sample->name;
            $groups[$key]['labels'] = $sample->labels;
            $groups[$key]['values'][] = (float) $sample->value;
        }

        $anomalies = [];

        foreach ($groups as $group) {
            $result = $this->detector->detect($group['values'], $zThreshold, minSamples: $minSamples);

            if ($result === null) {
                continue;
            }

            $anomalies[] = [
                'name' => $group['name'],
                'labels' => $group['labels'],
                ...$result,
            ];
        }

        usort($anomalies, fn (array $a, array $b) => abs($b['z_score']) <=> abs($a['z_score']));

        return $anomalies;
    }
}
