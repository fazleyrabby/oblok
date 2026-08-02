<?php

use App\Enums\AlertMetric;
use App\Enums\ProjectRole;
use App\Jobs\EvaluateAlertRulesJob;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\HealthCheckResult;
use App\Models\NotificationChannel;
use App\Models\NotificationDelivery;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('scheduled evaluation job triggers alert when service health breaches', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $service = Service::factory()->create([
        'project_id' => $project->id,
        'status' => 'failing',
    ]);

    HealthCheckResult::factory()->count(1)->create([
        'service_id' => $service->id,
        'status' => 'failing',
    ]);

    $channel = NotificationChannel::factory()->create([
        'project_id' => $project->id,
        'type' => 'webhook',
    ]);

    $rule = AlertRule::factory()->create([
        'project_id' => $project->id,
        'metric' => AlertMetric::ServiceHealth,
        'comparison' => 'equals',
        'consecutive_failures' => 1,
        'enabled' => true,
    ]);

    $rule->channels()->attach($channel->id);

    EvaluateAlertRulesJob::dispatchSync();

    expect($rule->fresh()->last_triggered_at)->not->toBeNull();
    $this->assertDatabaseHas('alert_events', [
        'project_id' => $project->id,
        'alert_rule_id' => $rule->id,
    ]);
});

test('scheduled evaluation job does not trigger when metric is healthy', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $service = Service::factory()->create([
        'project_id' => $project->id,
        'status' => 'healthy',
    ]);

    HealthCheckResult::factory()->count(3)->create([
        'service_id' => $service->id,
        'status' => 'healthy',
    ]);

    $rule = AlertRule::factory()->create([
        'project_id' => $project->id,
        'metric' => AlertMetric::ServiceHealth,
        'comparison' => 'equals',
        'consecutive_failures' => 1,
        'enabled' => true,
    ]);

    EvaluateAlertRulesJob::dispatchSync();

    expect($rule->fresh()->last_triggered_at)->toBeNull()
        ->and($rule->fresh()->last_evaluated_at)->not->toBeNull();
    $this->assertDatabaseCount('alert_events', 0);
});

test('member can view alert events for a project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => ProjectRole::Viewer->value]);

    $rule = AlertRule::factory()->create(['project_id' => $project->id]);
    AlertEvent::factory()->create([
        'project_id' => $project->id,
        'alert_rule_id' => $rule->id,
    ]);

    $this->actingAs($member)->get(route('projects.alerts.index', $project))
        ->assertOk();
});

test('non-member cannot view alert events', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)->get(route('projects.alerts.index', $project))
        ->assertForbidden();
});

test('owner can acknowledge a notification delivery via web', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $rule = AlertRule::factory()->create(['project_id' => $project->id]);
    $channel = NotificationChannel::factory()->create(['project_id' => $project->id]);
    $event = AlertEvent::factory()->create([
        'project_id' => $project->id,
        'alert_rule_id' => $rule->id,
    ]);
    $delivery = NotificationDelivery::factory()->create([
        'project_id' => $project->id,
        'alert_event_id' => $event->id,
        'alert_rule_id' => $rule->id,
        'notification_channel_id' => $channel->id,
    ]);

    $this->actingAs($owner)->post(route('projects.alerts.acknowledge', [$project, $delivery]))
        ->assertRedirect();

    expect($delivery->fresh()->status->value)->toBe('acknowledged')
        ->and($delivery->fresh()->acknowledged_by)->toBe($owner->id)
        ->and($delivery->fresh()->acknowledged_at)->not->toBeNull();
});

test('owner can snooze a notification delivery via API', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $rule = AlertRule::factory()->create(['project_id' => $project->id]);
    $channel = NotificationChannel::factory()->create(['project_id' => $project->id]);
    $event = AlertEvent::factory()->create([
        'project_id' => $project->id,
        'alert_rule_id' => $rule->id,
    ]);
    $delivery = NotificationDelivery::factory()->create([
        'project_id' => $project->id,
        'alert_event_id' => $event->id,
        'alert_rule_id' => $rule->id,
        'notification_channel_id' => $channel->id,
    ]);

    $until = now()->addHours(4)->format('Y-m-d H:i:s');

    $this->actingAs($owner)->postJson(route('api.v1.projects.alerts.snooze', [$project, $delivery]), [
        'until' => $until,
    ])->assertOk()
        ->assertJsonPath('data.status', 'snoozed');

    expect($delivery->fresh()->snoozed_until)->not->toBeNull();
});
