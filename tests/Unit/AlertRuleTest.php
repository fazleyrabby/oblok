<?php

use App\Actions\Alerts\DispatchAlertRule;
use App\Enums\AlertComparison;
use App\Enums\AlertMetric;
use App\Enums\DeliveryStatus;
use App\Enums\ProjectRole;
use App\Jobs\DeliverNotification;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\NotificationChannel;
use App\Models\Project;
use App\Models\User;
use App\Support\Alerts\MetricReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('alert rule evaluates metric readings against comparison', function () {
    $project = Project::factory()->create();
    $rule = AlertRule::factory()->create([
        'project_id' => $project->id,
        'metric' => AlertMetric::QueueBacklog,
        'comparison' => AlertComparison::Gt,
        'threshold' => 10,
    ]);

    $reading = new MetricReading(
        metric: AlertMetric::QueueBacklog,
        value: 15,
        occurredAt: now(),
    );

    expect($rule->evaluate($reading))->toBeTrue();

    $below = new MetricReading(
        metric: AlertMetric::QueueBacklog,
        value: 5,
        occurredAt: now(),
    );

    expect($rule->evaluate($below))->toBeFalse();
});

test('alert rule respects cooldown window', function () {
    $project = Project::factory()->create();
    $rule = AlertRule::factory()->create([
        'project_id' => $project->id,
        'cooldown_minutes' => 30,
        'last_triggered_at' => now()->subMinutes(5),
    ]);

    expect($rule->isInCooldown())->toBeTrue();

    $rule->update(['last_triggered_at' => now()->subMinutes(31)]);

    expect($rule->isInCooldown())->toBeFalse();
});

test('dispatch alert rule creates event and mail deliveries for role recipients', function () {
    Queue::fake();

    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $project->members()->attach($admin->id, ['role' => ProjectRole::Admin->value]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);

    $channel = NotificationChannel::factory()->create([
        'project_id' => $project->id,
        'type' => 'mail',
    ]);

    $rule = AlertRule::factory()->create([
        'project_id' => $project->id,
        'metric' => AlertMetric::QueueBacklog,
        'comparison' => AlertComparison::Gt,
        'threshold' => 5,
    ]);

    $rule->channels()->attach($channel->id);

    $reading = new MetricReading(
        metric: AlertMetric::QueueBacklog,
        value: 20,
        occurredAt: now(),
        context: ['pending_jobs' => 20],
    );

    $event = app(DispatchAlertRule::class)->handle($rule, $reading);

    expect($event)->toBeInstanceOf(AlertEvent::class)
        ->and($event->deliveries)->toHaveCount(1)
        ->and($event->deliveries->first()->status)->toBe(DeliveryStatus::Pending)
        ->and($event->deliveries->first()->payload)->toBe(['pending_jobs' => 20]);

    Queue::assertPushed(DeliverNotification::class, function ($job) use ($admin) {
        return $job->recipient?->id === $admin->id;
    });
});

test('dispatch alert rule is skipped while in cooldown', function () {
    Queue::fake();

    $project = Project::factory()->create();
    $channel = NotificationChannel::factory()->create([
        'project_id' => $project->id,
        'type' => 'webhook',
    ]);

    $rule = AlertRule::factory()->create([
        'project_id' => $project->id,
        'last_triggered_at' => now()->subMinutes(1),
    ]);

    $rule->channels()->attach($channel->id);

    $reading = new MetricReading(
        metric: AlertMetric::ServiceHealth,
        value: 1,
        occurredAt: now(),
    );

    $event = app(DispatchAlertRule::class)->handle($rule, $reading);

    expect($event)->toBeNull();
    Queue::assertNothingPushed();
});
