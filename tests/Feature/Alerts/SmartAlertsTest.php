<?php

use App\Actions\Alerts\DispatchAlertRule;
use App\Actions\Alerts\ResolveAlertEvent;
use App\Actions\Dashboard\GetDashboardOverview;
use App\Actions\Services\PingServiceHealth;
use App\Enums\AlertComparison;
use App\Enums\AlertMetric;
use App\Enums\AlertSeverity;
use App\Events\AlertResolved;
use App\Events\ServiceFlappingChanged;
use App\Events\ServiceStatusChanged;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\HealthCheckResult;
use App\Models\Incident;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use App\Services\Monitoring\HealthCheckerRegistry;
use App\Services\Monitoring\HealthCheckResultData;
use App\Support\Alerts\MetricReading;
use App\Support\Alerts\Sources\ServiceHealthMetricSource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->project = Project::factory()->create();
    $this->service = Service::factory()->create([
        'project_id' => $this->project->id,
        'status' => 'healthy',
        'is_flapping' => false,
    ]);
});

// =============================================================================
// Flapping Detection
// =============================================================================

test('service transitions into flapping state after 3 changes', function () {
    Event::fake([ServiceStatusChanged::class, ServiceFlappingChanged::class]);

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
    Event::fake([ServiceStatusChanged::class, ServiceFlappingChanged::class]);
    mockHealthCheckResponse('healthy');
    app(PingServiceHealth::class)->handle($this->service);
    expect($this->service->fresh()->is_flapping)->toBeFalse();
    Event::assertDispatched(ServiceStatusChanged::class);

    // Transition 3: healthy -> failing
    Event::fake([ServiceStatusChanged::class, ServiceFlappingChanged::class]);
    mockHealthCheckResponse('failing');
    app(PingServiceHealth::class)->handle($this->service);

    // Now transitions count over the last 10 checks is 3 (healthy->failing, failing->healthy, healthy->failing)
    expect($this->service->fresh()->is_flapping)->toBeTrue();
    // Event should be suppressed because it entered flapping state
    Event::assertNotDispatched(ServiceStatusChanged::class);
    // But flapping change should be dispatched
    Event::assertDispatched(ServiceFlappingChanged::class, fn ($e) => $e->isFlapping === true);
});

test('service exits flapping state when status stabilizes', function () {
    Event::fake([ServiceStatusChanged::class, ServiceFlappingChanged::class]);

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
    // Flapping change should be dispatched with isFlapping = false
    Event::assertDispatched(ServiceFlappingChanged::class, fn ($e) => $e->isFlapping === false);
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

// =============================================================================
// Incident Grouping
// =============================================================================

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

// =============================================================================
// Alert Deduplication
// =============================================================================

test('duplicate alert events are suppressed when alert is already firing', function () {
    Event::fake();

    $rule = AlertRule::factory()->create([
        'project_id' => $this->project->id,
        'metric' => AlertMetric::ServiceHealth,
        'comparison' => AlertComparison::Gt,
        'consecutive_failures' => 1,
        'cooldown_minutes' => 0,
    ]);

    $reading = new MetricReading(
        metric: AlertMetric::ServiceHealth,
        value: 3,
        occurredAt: now(),
        context: ['service_id' => $this->service->id],
    );

    $dispatch = app(DispatchAlertRule::class);

    // First dispatch should create an event.
    $event1 = $dispatch->handle($rule, $reading);
    expect($event1)->not->toBeNull();
    expect($event1->state)->toBe('firing');

    // Refresh the rule to pick up the active_event_id.
    $rule->refresh();
    expect($rule->isFiring())->toBeTrue();
    expect($rule->active_event_id)->toBe($event1->id);

    // Second dispatch should be deduplicated — returns null, no new event.
    $event2 = $dispatch->handle($rule->fresh(), $reading);
    expect($event2)->toBeNull();

    // Only one alert event should exist.
    expect(AlertEvent::where('alert_rule_id', $rule->id)->count())->toBe(1);
});

// =============================================================================
// Alert Resolution
// =============================================================================

test('alert event auto-resolves when condition clears', function () {
    Event::fake();

    $rule = AlertRule::factory()->create([
        'project_id' => $this->project->id,
        'metric' => AlertMetric::ServiceHealth,
        'comparison' => AlertComparison::Gt,
        'consecutive_failures' => 1,
    ]);

    // Create a firing event and attach it to the rule.
    $firingEvent = AlertEvent::create([
        'alert_rule_id' => $rule->id,
        'project_id' => $this->project->id,
        'severity' => $rule->severity,
        'subject' => $rule->name,
        'state' => 'firing',
        'fingerprint' => 'test-fingerprint',
        'triggered_at' => now(),
    ]);

    $rule->update(['active_event_id' => $firingEvent->id]);

    // Resolve the event.
    $resolve = app(ResolveAlertEvent::class);
    $resolved = $resolve->handle($firingEvent);

    expect($resolved->state)->toBe('resolved');
    expect($resolved->resolved_at)->not->toBeNull();
    expect($rule->fresh()->active_event_id)->toBeNull();

    Event::assertDispatched(AlertResolved::class, fn ($e) => $e->alertEvent->id === $firingEvent->id);
});

test('resolved alert can re-fire after condition returns', function () {
    Event::fake();

    $rule = AlertRule::factory()->create([
        'project_id' => $this->project->id,
        'metric' => AlertMetric::ServiceHealth,
        'comparison' => AlertComparison::Gt,
        'consecutive_failures' => 1,
        'cooldown_minutes' => 0,
    ]);

    $reading = new MetricReading(
        metric: AlertMetric::ServiceHealth,
        value: 3,
        occurredAt: now(),
        context: ['service_id' => $this->service->id],
    );

    $dispatch = app(DispatchAlertRule::class);
    $resolve = app(ResolveAlertEvent::class);

    // 1. Fire the alert.
    $event1 = $dispatch->handle($rule, $reading);
    expect($event1)->not->toBeNull();

    // 2. Resolve it.
    $resolve->handle($event1);
    $rule->refresh();
    expect($rule->isFiring())->toBeFalse();

    // 3. Fire again — should create a new event.
    $event2 = $dispatch->handle($rule->fresh(), $reading);
    expect($event2)->not->toBeNull();
    expect($event2->id)->not->toBe($event1->id);

    // Two total events should exist.
    expect(AlertEvent::where('alert_rule_id', $rule->id)->count())->toBe(2);
});

// =============================================================================
// Flapping Broadcast Event
// =============================================================================

test('flapping state change dispatches broadcast event', function () {
    Event::fake([ServiceFlappingChanged::class, ServiceStatusChanged::class]);

    // Entering flapping state.
    $this->service->update(['is_flapping' => false, 'status' => 'healthy']);

    // Create enough transitions to trigger flapping.
    HealthCheckResult::factory()->create(['service_id' => $this->service->id, 'status' => 'healthy']);
    HealthCheckResult::factory()->create(['service_id' => $this->service->id, 'status' => 'failing']);
    HealthCheckResult::factory()->create(['service_id' => $this->service->id, 'status' => 'healthy']);
    HealthCheckResult::factory()->create(['service_id' => $this->service->id, 'status' => 'failing']);

    // This check adds another transition, totaling >= 3.
    mockHealthCheckResponse('healthy');
    app(PingServiceHealth::class)->handle($this->service);

    Event::assertDispatched(ServiceFlappingChanged::class, function ($event) {
        return $event->service->id === $this->service->id
            && $event->project->id === $this->project->id;
    });
});

// =============================================================================
// Dashboard Data
// =============================================================================

test('dashboard overview includes active alerts and flapping services', function () {
    $user = User::factory()->create();
    $this->project->update(['user_id' => $user->id]);

    // Create a firing alert event.
    AlertEvent::create([
        'alert_rule_id' => AlertRule::factory()->create(['project_id' => $this->project->id])->id,
        'project_id' => $this->project->id,
        'severity' => AlertSeverity::Warning,
        'subject' => 'Test alert',
        'state' => 'firing',
        'triggered_at' => now(),
    ]);

    // Mark a service as flapping.
    $this->service->update(['is_flapping' => true]);

    // Create an open incident.
    Incident::create([
        'project_id' => $this->project->id,
        'title' => 'Test incident',
        'severity' => 'high',
        'status' => 'investigating',
        'started_at' => now(),
    ]);

    $overview = app(GetDashboardOverview::class)->handle($user);

    expect($overview['active_alerts'])->toBe(1);
    expect($overview['flapping_services'])->toBe(1);
    expect($overview['active_incidents'])->toBe(1);
});

test('dashboard overview returns zero for healthy state', function () {
    $user = User::factory()->create();
    $this->project->update(['user_id' => $user->id]);

    $overview = app(GetDashboardOverview::class)->handle($user);

    expect($overview['active_alerts'])->toBe(0);
    expect($overview['flapping_services'])->toBe(0);
    expect($overview['active_incidents'])->toBe(0);
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

