<?php

namespace App\Services\Metrics;

/**
 * Detects anomalies in a numeric time series using a z-score comparison.
 *
 * The most recent portion of the series (the "recent window") is compared
 * against the earlier portion (the "baseline"). A series is flagged when the
 * recent mean deviates from the baseline mean by more than a configurable
 * number of baseline standard deviations. A series needs a minimum number of
 * samples before it is considered at all, so sparse data is never flagged.
 */
class AnomalyDetector
{
    /**
     * Evaluate a single chronological series of values.
     *
     * @param  array<int, float>  $series  Chronologically ordered values.
     * @return array{baseline_mean: float, current_mean: float, z_score: float, percent_change: float|null, direction: 'up'|'down', severity: 'warning'|'critical', sample_count: int}|null Null when the series is too small or not anomalous.
     */
    public function detect(array $series, float $zThreshold = 3.0, float $recentRatio = 0.2, int $minSamples = 12): ?array
    {
        $count = count($series);

        if ($count < $minSamples) {
            return null;
        }

        $baselineCount = (int) floor($count * (1 - $recentRatio));
        $baselineCount = max(1, min($baselineCount, $count - 1));

        $baseline = array_slice($series, 0, $baselineCount);
        $recent = array_slice($series, $baselineCount);

        $baselineMean = $this->mean($baseline);
        $baselineStd = $this->stddev($baseline, $baselineMean);
        $recentMean = $this->mean($recent);

        // A perfectly flat baseline has zero standard deviation, which would
        // turn any shift into an infinite z-score. Floor the spread at a small
        // relative fraction of the baseline mean so sub-noise fluctuations are
        // not flagged as anomalies while genuine shifts still are.
        $floor = max(abs($baselineMean) * 0.05, 1e-9);

        $zScore = ($recentMean - $baselineMean) / max($baselineStd, $floor);

        if (abs($zScore) < $zThreshold) {
            return null;
        }

        $percentChange = $baselineMean == 0.0
            ? null
            : (($recentMean - $baselineMean) / abs($baselineMean)) * 100;

        return [
            'baseline_mean' => round($baselineMean, 4),
            'current_mean' => round($recentMean, 4),
            'z_score' => round($zScore, 2),
            'percent_change' => $percentChange === null ? null : round($percentChange, 2),
            'direction' => $zScore > 0 ? 'up' : 'down',
            'severity' => abs($zScore) >= 5 ? 'critical' : 'warning',
            'sample_count' => $count,
        ];
    }

    /**
     * Compute the arithmetic mean of a list of values.
     *
     * @param  array<int, float>  $values
     */
    protected function mean(array $values): float
    {
        return array_sum($values) / count($values);
    }

    /**
     * Compute the sample standard deviation of a list of values.
     *
     * @param  array<int, float>  $values
     */
    protected function stddev(array $values, float $mean): float
    {
        $variance = array_sum(array_map(
            fn (float $value): float => ($value - $mean) ** 2,
            $values
        )) / max(1, count($values) - 1);

        return sqrt($variance);
    }
}
