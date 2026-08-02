<?php

use App\Enums\ProjectRole;
use App\Models\AlertRule;
use App\Models\NotificationChannel;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can create an alert rule and attach channels via web', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $channel = NotificationChannel::factory()->create(['project_id' => $project->id]);

    $response = $this->actingAs($owner)->post(route('projects.alert-rules.store', $project), [
        'name' => 'Queue Backlog Too High',
        'metric' => 'queue_backlog',
        'comparison' => 'gt',
        'threshold' => 50,
        'window_minutes' => 5,
        'severity' => 'critical',
        'cooldown_minutes' => 15,
        'enabled' => 1,
        'channel_ids' => [$channel->id],
    ]);

    $response->assertRedirect(route('projects.alert-rules.index', $project));
    $this->assertDatabaseHas('alert_rules', [
        'project_id' => $project->id,
        'name' => 'Queue Backlog Too High',
        'metric' => 'queue_backlog',
    ]);
    $this->assertDatabaseHas('alert_rule_channel', [
        'alert_rule_id' => AlertRule::where('name', 'Queue Backlog Too High')->first()->id,
        'notification_channel_id' => $channel->id,
    ]);
});

test('viewer cannot create an alert rule', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);

    $this->actingAs($viewer)->post(route('projects.alert-rules.store', $project), [
        'name' => 'Nope',
        'metric' => 'queue_backlog',
        'comparison' => 'gt',
        'window_minutes' => 5,
        'severity' => 'warning',
        'cooldown_minutes' => 15,
    ])->assertForbidden();
});

test('owner can update and delete an alert rule', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $channel = NotificationChannel::factory()->create(['project_id' => $project->id]);
    $rule = AlertRule::factory()->create(['project_id' => $project->id]);
    $rule->channels()->attach($channel->id);

    $this->actingAs($owner)->put(route('projects.alert-rules.update', [$project, $rule]), [
        'name' => 'Updated Rule',
        'metric' => 'queue_backlog',
        'comparison' => 'gt',
        'threshold' => 10,
        'window_minutes' => 10,
        'severity' => 'warning',
        'cooldown_minutes' => 30,
        'enabled' => 1,
        'channel_ids' => [],
    ])->assertRedirect(route('projects.alert-rules.index', $project));

    $this->assertDatabaseHas('alert_rules', [
        'id' => $rule->id,
        'name' => 'Updated Rule',
    ]);
    $this->assertDatabaseMissing('alert_rule_channel', ['alert_rule_id' => $rule->id]);

    $this->actingAs($owner)->delete(route('projects.alert-rules.destroy', [$project, $rule]))
        ->assertRedirect(route('projects.alert-rules.index', $project));

    $this->assertDatabaseMissing('alert_rules', ['id' => $rule->id]);
});

test('admin can create an alert rule via API', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($admin->id, ['role' => ProjectRole::Admin->value]);

    $response = $this->actingAs($admin)->postJson(route('api.v1.projects.alert-rules.store', $project), [
        'name' => 'Deployment Failed Alert',
        'metric' => 'deployment_status',
        'comparison' => 'equals',
        'threshold' => 1,
        'window_minutes' => 5,
        'severity' => 'critical',
        'cooldown_minutes' => 10,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.metric', 'deployment_status')
        ->assertJsonStructure(['data' => ['id', 'project_id', 'name', 'metric', 'comparison', 'severity', 'enabled']]);
});

test('operator cannot create an alert rule via API', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);

    $this->actingAs($operator)->postJson(route('api.v1.projects.alert-rules.store', $project), [
        'name' => 'Nope',
        'metric' => 'queue_backlog',
        'comparison' => 'gt',
        'window_minutes' => 5,
        'severity' => 'warning',
        'cooldown_minutes' => 15,
    ])->assertForbidden();
});

test('alert rule validation rejects invalid metric', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.alert-rules.store', $project), [
        'name' => 'Bad Metric',
        'metric' => 'does_not_exist',
        'comparison' => 'gt',
        'window_minutes' => 5,
        'severity' => 'warning',
        'cooldown_minutes' => 15,
    ])->assertUnprocessable();
});
