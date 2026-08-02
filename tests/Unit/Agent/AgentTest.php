<?php

use OblokAgent\AccessLogParser;
use OblokAgent\Config;
use OblokAgent\FileTailer;
use OblokAgent\LogLineParser;
use OblokAgent\RequestMetricsAggregator;

test('config reads environment values', function () {
    $config = Config::fromEnv([
        'OBLOK_URL' => 'https://oblok.lan/',
        'OBLOK_API_KEY' => 'atl_key',
        'OBLOK_PROJECT_ID' => 'abc',
        'OBLOK_LOG_FILES' => ' /a.log, /b.log ',
        'OBLOK_ACCESS_LOG' => '/var/log/nginx/access.log',
        'OBLOK_POLL_INTERVAL' => '4',
    ]);

    expect($config->baseUrl)->toBe('https://oblok.lan')
        ->and($config->apiKey)->toBe('atl_key')
        ->and($config->projectId)->toBe('abc')
        ->and($config->logFiles)->toBe(['/a.log', '/b.log'])
        ->and($config->accessLogFile)->toBe('/var/log/nginx/access.log')
        ->and($config->pollInterval)->toBe(4)
        ->and($config->flushInterval)->toBe(10);
});

test('config requires nothing but tolerates missing optional values', function () {
    $config = Config::fromEnv([
        'OBLOK_URL' => 'https://oblok.lan',
        'OBLOK_API_KEY' => 'atl_key',
        'OBLOK_PROJECT_ID' => 'abc',
    ]);

    expect($config->logFiles)->toBe([])
        ->and($config->accessLogFile)->toBeNull();
});

test('config expands glob patterns into concrete log files', function () {
    $dir = sys_get_temp_dir().'/oblok-agent-glob-'.uniqid();
    mkdir($dir);
    file_put_contents($dir.'/laravel-2026-08-02.log', 'one');
    file_put_contents($dir.'/laravel-2026-08-03.log', 'two');

    $config = Config::fromEnv([
        'OBLOK_URL' => 'https://oblok.lan',
        'OBLOK_API_KEY' => 'atl_key',
        'OBLOK_PROJECT_ID' => 'abc',
        'OBLOK_LOG_FILES' => $dir.'/laravel-*.log',
    ]);

    $files = $config->resolveLogFiles();

    expect($files)->toContain($dir.'/laravel-2026-08-02.log')
        ->and($files)->toContain($dir.'/laravel-2026-08-03.log');

    foreach ($files as $file) {
        @unlink($file);
    }
    @rmdir($dir);
});

test('log line parser extracts json entries', function () {
    $parser = new LogLineParser;

    $entry = $parser->parse('{"message":"Order processed","level":"error","context":{"id":42}}');

    expect($entry['message'])->toBe('Order processed')
        ->and($entry['level'])->toBe('error')
        ->and($entry['context'])->toBe(['id' => 42]);
});

test('log line parser extracts laravel text entries', function () {
    $parser = new LogLineParser;

    $entry = $parser->parse('[2026-08-02 12:00:00] production.ERROR: Something failed.');

    expect($entry['message'])->toBe('Something failed.')
        ->and($entry['level'])->toBe('error');
});

test('log line parser defaults unknown lines and levels to info', function () {
    $parser = new LogLineParser;

    expect($parser->parse('just a plain line')['level'])->toBe('info')
        ->and($parser->parse('{"message":"x","level":"MYSTERY"}')['level'])->toBe('info');
});

test('access log parser extracts combined format lines', function () {
    $parser = new AccessLogParser;

    $request = $parser->parse('127.0.0.1 - - [26/May/2026:10:00:00 +0000] "GET /api/users HTTP/1.1" 200 612 "-" "curl/8.0" 0.045');

    expect($request)->not->toBeNull()
        ->and($request['method'])->toBe('GET')
        ->and($request['status'])->toBe(200)
        ->and($request['request_time'])->toBe(0.045);
});

test('access log parser rejects malformed lines', function () {
    $parser = new AccessLogParser;

    expect($parser->parse('not an access log line'))->toBeNull()
        ->and($parser->parse(''))->toBeNull();
});

test('request metrics aggregator flushes per method and status', function () {
    $aggregator = new RequestMetricsAggregator;
    $aggregator->add(['method' => 'GET', 'path' => '/x', 'status' => 200, 'request_time' => 0.1]);
    $aggregator->add(['method' => 'GET', 'path' => '/x', 'status' => 200, 'request_time' => 0.3]);
    $aggregator->add(['method' => 'POST', 'path' => '/y', 'status' => 500, 'request_time' => 0.2]);

    $metrics = $aggregator->flush('http_requests', '2026-08-02T12:00:00+00:00');

    expect($metrics)->toHaveCount(4)
        ->and($metrics[0]['name'])->toBe('http_requests_total')
        ->and($metrics[0]['labels'])->toBe(['method' => 'GET', 'status' => '200'])
        ->and($metrics[0]['value'])->toBe(2)
        ->and($metrics[1]['name'])->toBe('http_requests_duration_seconds')
        ->and($metrics[1]['value'])->toBe(0.2);
});

test('request metrics aggregator resets after flush', function () {
    $aggregator = new RequestMetricsAggregator;
    $aggregator->add(['method' => 'GET', 'path' => '/', 'status' => 200, 'request_time' => null]);

    $aggregator->flush('http_requests', '2026-08-02T12:00:00+00:00');
    $metrics = $aggregator->flush('http_requests', '2026-08-02T12:00:00+00:00');

    expect($metrics)->toBe([]);
});

test('file tailer returns only newly appended lines', function () {
    $file = tempnam(sys_get_temp_dir(), 'oblok-agent-');
    file_put_contents($file, "first line\n");

    $tailer = new FileTailer($file);

    expect($tailer->readNewLines())->toBe([]);

    file_put_contents($file, "second line\nthird line\n", FILE_APPEND);

    expect($tailer->readNewLines())->toBe(['second line', 'third line']);

    @unlink($file);
});

test('file tailer resumes after truncation without re-reading old content', function () {
    $file = tempnam(sys_get_temp_dir(), 'oblok-agent-');
    file_put_contents($file, "old line\n");

    $tailer = new FileTailer($file);
    $tailer->readNewLines();

    file_put_contents($file, '');

    expect($tailer->readNewLines())->toBe([]);

    file_put_contents($file, "fresh start\n", FILE_APPEND);

    expect($tailer->readNewLines())->toBe(['fresh start']);

    @unlink($file);
});
