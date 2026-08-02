<?php

use App\Enums\ProjectRole;
use App\Models\NotificationChannel;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can create a notification channel via web', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.notification-channels.store', $project), [
        'name' => 'Team Slack Alerts',
        'type' => 'slack',
        'config' => ['webhook_url' => 'https://hooks.slack.com/services/T000/B000/XXXX'],
        'enabled' => 1,
    ]);

    $response->assertRedirect(route('projects.notification-channels.index', $project));
    $this->assertDatabaseHas('notification_channels', [
        'project_id' => $project->id,
        'name' => 'Team Slack Alerts',
        'type' => 'slack',
    ]);
});

test('viewer cannot create a notification channel', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);

    $this->actingAs($viewer)->post(route('projects.notification-channels.store', $project), [
        'name' => 'Nope',
        'type' => 'slack',
    ])->assertForbidden();
});

test('owner can update and delete a notification channel', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $channel = NotificationChannel::factory()->create([
        'project_id' => $project->id,
        'type' => 'webhook',
    ]);

    $this->actingAs($owner)->put(route('projects.notification-channels.update', [$project, $channel]), [
        'name' => 'Updated Channel',
        'type' => 'webhook',
        'enabled' => 1,
    ])->assertRedirect(route('projects.notification-channels.index', $project));

    $this->assertDatabaseHas('notification_channels', [
        'id' => $channel->id,
        'name' => 'Updated Channel',
    ]);

    $this->actingAs($owner)->delete(route('projects.notification-channels.destroy', [$project, $channel]))
        ->assertRedirect(route('projects.notification-channels.index', $project));

    $this->assertDatabaseMissing('notification_channels', ['id' => $channel->id]);
});

test('a non-member cannot view notification channels', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)->get(route('projects.notification-channels.index', $project))
        ->assertForbidden();
});

test('owner can create a notification channel via API', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($owner)->postJson(route('api.v1.projects.notification-channels.store', $project), [
        'name' => 'API Webhook',
        'type' => 'webhook',
        'config' => ['url' => 'https://example.com/hooks', 'secret' => 'shh'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.type', 'webhook')
        ->assertJsonStructure(['data' => ['id', 'project_id', 'name', 'type', 'enabled']]);
});

test('operator cannot create a notification channel via API', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);

    $this->actingAs($operator)->postJson(route('api.v1.projects.notification-channels.store', $project), [
        'name' => 'Nope',
        'type' => 'webhook',
    ])->assertForbidden();
});

test('channel config is encrypted at rest', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.notification-channels.store', $project), [
        'name' => 'Secret Webhook',
        'type' => 'webhook',
        'config' => ['url' => 'https://example.com/hooks', 'secret' => 'top-secret'],
    ])->assertCreated();

    $channel = NotificationChannel::where('name', 'Secret Webhook')->first();
    $raw = $channel->getRawOriginal('encrypted_config');

    expect($raw)->not->toContain('top-secret');
});
