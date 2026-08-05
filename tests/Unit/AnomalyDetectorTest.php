<?php

use App\Services\Metrics\AnomalyDetector;

it('returns null when the series is too small', function () {
    $detector = new AnomalyDetector;

    expect($detector->detect([1, 2, 3]))->toBeNull();
});

it('does not flag a stable series', function () {
    $detector = new AnomalyDetector;

    $series = collect(range(1, 100))
        ->map(fn () => 50.0)
        ->all();

    expect($detector->detect($series))->toBeNull();
});

it('flags an upward spike against a flat baseline', function () {
    $detector = new AnomalyDetector;

    $series = array_merge(
        array_fill(0, 20, 10.0),
        array_fill(0, 5, 100.0)
    );

    $result = $detector->detect($series);

    expect($result)->not->toBeNull();
    expect($result['direction'])->toBe('up');
    expect($result['z_score'])->toBeGreaterThan(3);
    expect($result['current_mean'])->toBeGreaterThan($result['baseline_mean']);
});

it('flags a downward drop against a stable baseline', function () {
    $detector = new AnomalyDetector;

    $series = array_merge(
        array_fill(0, 20, 80.0),
        array_fill(0, 5, 5.0)
    );

    $result = $detector->detect($series);

    expect($result)->not->toBeNull();
    expect($result['direction'])->toBe('down');
    expect($result['z_score'])->toBeLessThan(-3);
});

it('ignores small fluctuations', function () {
    $detector = new AnomalyDetector;

    $series = array_merge(
        array_fill(0, 20, 10.0),
        array_fill(0, 5, 11.0)
    );

    expect($detector->detect($series))->toBeNull();
});

it('grades severe anomalies as critical', function () {
    $detector = new AnomalyDetector;

    $series = array_merge(
        array_fill(0, 20, 10.0),
        array_fill(0, 5, 500.0)
    );

    $result = $detector->detect($series);

    expect($result['severity'])->toBe('critical');
});

it('respects a custom z-score threshold', function () {
    $detector = new AnomalyDetector;

    $series = array_merge(
        array_fill(0, 20, 10.0),
        array_fill(0, 5, 100.0)
    );

    // With a lenient threshold the same data is not anomalous.
    expect($detector->detect($series, zThreshold: 500.0))->toBeNull();

    // With the default threshold it is.
    expect($detector->detect($series))->not->toBeNull();
});
