<?php

use App\Actions\Services\PingServiceHealth;
use App\Jobs\CheckServiceHealthJob;
use App\Jobs\DispatchScheduledHealthChecksJob;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('ping service health action probes target and records healthy metric', function () {
    Http::fake([
        'https://api.stripe.com/health' => Http::response([], 200),
    ]);

    $project = Project::factory()->create();
    $service = Service::factory()->create([
        'project_id' => $project->id,
        'target' => 'https://api.stripe.com/health',
        'expected_status_code' => 200,
        'status' => 'unknown',
    ]);

    $action = app(PingServiceHealth::class);
    $result = $action->handle($service);

    expect($result->status)->toBe('healthy')
        ->and($result->status_code)->toBe(200)
        ->and($service->fresh()->status)->toBe('healthy');
});

test('scheduled job dispatches check jobs for due services', function () {
    Queue::fake();

    $project = Project::factory()->create();
    $dueService = Service::factory()->create([
        'project_id' => $project->id,
        'last_checked_at' => now()->subMinutes(5),
    ]);

    $job = new DispatchScheduledHealthChecksJob;
    $job->handle();

    Queue::assertPushed(CheckServiceHealthJob::class, function ($job) use ($dueService) {
        return $job->service->id === $dueService->id;
    });
});
