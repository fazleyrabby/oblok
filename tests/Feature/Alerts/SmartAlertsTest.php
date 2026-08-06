<?php

use App\Actions\Services\PingServiceHealth;
use App\Enums\AlertMetric;
use App\Events\ServiceStatusChanged;
use App\Models\AlertRule;
use App\Models\HealthCheckResult;
use App\Models\Incident;
use App\Models\Project;
use App\Models\Service;
use App\Services\Monitoring\HealthCheckerRegistry;
use App\Services\Monitoring\HealthCheckResultData;
use App\Support\Alerts\Sources\ServiceHealthMetricSource;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->project = Project::factory()->create();
    $this->service = Service::factory()->create([
        'project_id' => $this->project->id,
        'status' => 'healthy',
        'is_flapping' => false,
    ]);
});

test('service transitions into flapping state after 3 changes', function () {
    Event::fake([ServiceStatusChanged::class]);

    // Record some initial healthy checks (no transitions)
    HealthCheckResult::factory()->create(['service_id' => $this->service->id, 'status' => 'healthy']);
    HealthCheckResult::factory()->create(['service_id' => $this->service->id, 'status' => 'healthy']);

    // Transition 1: healthy -> failing
    $this->service->status = 'healthy';
    $this->service->save();
    mockHealthCheckResponse('failing');
    app(PingServiceHealth::class)->handle($this->service);
    expect($this->service->fresh()->is_flapping)->toBeFalse();
    Event::assertDispatched(ServiceStatusChanged::class);

    // Transition 2: failing -> healthy
    Event::fake([ServiceStatusChanged::class]);
    mockHealthCheckResponse('healthy');
    app(PingServiceHealth::class)->handle($this->service);
    expect($this->service->fresh()->is_flapping)->toBeFalse();
    Event::assertDispatched(ServiceStatusChanged::class);

    // Transition 3: healthy -> failing
    Event::fake([ServiceStatusChanged::class]);
    mockHealthCheckResponse('failing');
    app(PingServiceHealth::class)->handle($this->service);

    // Now transitions count over the last 10 checks is 3 (healthy->failing, failing->healthy, healthy->failing)
    expect($this->service->fresh()->is_flapping)->toBeTrue();
    // Event should be suppressed because it entered flapping state
    Event::assertNotDispatched(ServiceStatusChanged::class);
});

test('service exits flapping state when status stabilizes', function () {
    Event::fake([ServiceStatusChanged::class]);

    // Force service to be flapping
    $this->service->update(['is_flapping' => true, 'status' => 'failing']);

    // Create 10 consecutive failing results to clear transitions to 0
    for ($i = 0; $i < 10; $i++) {
        HealthCheckResult::factory()->create([
            'service_id' => $this->service->id,
            'status' => 'failing',
            'created_at' => now()->addMinutes($i),
        ]);
    }

    mockHealthCheckResponse('failing');
    app(PingServiceHealth::class)->handle($this->service);

    // Transitions count is 0 (< 2), so it exits flapping
    expect($this->service->fresh()->is_flapping)->toBeFalse();
    // Exiting flapping state should force status evaluation event
    Event::assertDispatched(ServiceStatusChanged::class);
});

test('flapping service is excluded from health alerts evaluation', function () {
    $rule = AlertRule::factory()->create([
        'project_id' => $this->project->id,
        'metric' => AlertMetric::ServiceHealth,
        'consecutive_failures' => 1,
    ]);

    HealthCheckResult::factory()->create([
        'service_id' => $this->service->id,
        'status' => 'failing',
    ]);

    $source = new ServiceHealthMetricSource;

    // 1. When service is not flapping, it should return failure reading
    $reading = $source->readingFor($rule);
    expect($reading)->not->toBeNull();
    expect($reading->value)->toBe(1);

    // 2. When service is flapping, it should be excluded
    $this->service->update(['is_flapping' => true]);
    $reading = $source->readingFor($rule);
    expect($reading)->toBeNull();
});

test('incident grouping maps concurrent service failures under one incident', function () {
    $serviceA = $this->service;
    $serviceB = Service::factory()->create([
        'project_id' => $this->project->id,
        'status' => 'healthy',
    ]);

    // 1. Service A fails -> creates a new Incident
    mockHealthCheckResponse('failing');
    app(PingServiceHealth::class)->handle($serviceA);

    $incidents = Incident::where('project_id', $this->project->id)->open()->get();
    expect($incidents)->toHaveCount(1);
    expect($incidents->first()->service_id)->toBe($serviceA->id);
    expect($incidents->first()->title)->toContain($serviceA->name);

    // 2. Service B fails -> groups under existing Incident
    mockHealthCheckResponse('failing');
    app(PingServiceHealth::class)->handle($serviceB);

    $incidents = Incident::where('project_id', $this->project->id)->open()->get();
    expect($incidents)->toHaveCount(1); // Still only 1 open incident!

    $groupedIncident = $incidents->first();
    expect($groupedIncident->service_id)->toBeNull(); // Becomes project-wide
    expect($groupedIncident->title)->toBe('Multiple Service Failures Detected');
    expect($groupedIncident->description)->toContain($serviceB->name);
});

test('incident is auto-resolved only when all failing services recover', function () {
    $serviceA = $this->service;
    $serviceB = Service::factory()->create([
        'project_id' => $this->project->id,
        'status' => 'healthy',
    ]);

    // Fail both services to create a grouped incident
    mockHealthCheckResponse('failing');
    app(PingServiceHealth::class)->handle($serviceA);

    mockHealthCheckResponse('failing');
    app(PingServiceHealth::class)->handle($serviceB);

    $incident = Incident::where('project_id', $this->project->id)->open()->first();
    expect($incident)->not->toBeNull();

    // 1. Service A recovers -> incident stays open because Service B is still failing
    mockHealthCheckResponse('healthy');
    app(PingServiceHealth::class)->handle($serviceA);

    expect($incident->fresh()->isResolved())->toBeFalse();

    // 2. Service B recovers -> incident is auto-resolved since no other services are failing
    mockHealthCheckResponse('healthy');
    app(PingServiceHealth::class)->handle($serviceB);

    expect($incident->fresh()->isResolved())->toBeTrue();
});

/**
 * Helper to mock health check response from registry.
 */
function mockHealthCheckResponse(string $status)
{
    $resultData = new HealthCheckResultData(
        status: $status,
        statusCode: $status === 'healthy' ? 200 : 500,
        responseTimeMs: 120,
        errorMessage: $status === 'healthy' ? null : 'Failed connection'
    );

    // Bind instance in container
    $registry = Mockery::mock(HealthCheckerRegistry::class);
    $registry->shouldReceive('check')->andReturn($resultData);
    app()->instance(HealthCheckerRegistry::class, $registry);
}
