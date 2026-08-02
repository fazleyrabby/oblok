<?php

use App\Enums\MessagingPlatform;
use App\Enums\ProjectRole;
use App\Models\MessagingIntegration;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('owner can connect a slack workspace via web', function () {
    Http::fake([
        'https://slack.com/api/auth.test' => Http::response([
            'ok' => true,
            'team' => 'Acme Corp',
            'team_id' => 'T123',
            'user_id' => 'U123',
        ]),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->post(route('projects.messaging.store', $project), [
        'platform' => 'slack',
        'bot_token' => 'xoxb-valid-token-value',
    ])->assertRedirect(route('projects.messaging.index', $project));

    $integration = $project->messagingIntegration;

    expect($integration)->not->toBeNull()
        ->and($integration->name)->toBe('Acme Corp')
        ->and($integration->platform)->toBe(MessagingPlatform::Slack)
        ->and($integration->getRawOriginal('config'))->not->toContain('xoxb-valid-token-value');
});

test('invalid slack token is rejected with an error', function () {
    Http::fake([
        'https://slack.com/api/auth.test' => Http::response(['ok' => false, 'error' => 'invalid_auth']),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->post(route('projects.messaging.store', $project), [
        'platform' => 'slack',
        'bot_token' => 'xoxb-invalid-token',
    ])->assertSessionHasErrors('bot_token');

    $this->assertDatabaseCount('messaging_integrations', 0);
});

test('operator cannot connect a messaging integration', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);

    $this->actingAs($operator)->post(route('projects.messaging.store', $project), [
        'platform' => 'slack',
        'bot_token' => 'xoxb-valid-token-value',
    ])->assertForbidden();

    $this->assertDatabaseCount('messaging_integrations', 0);
});

test('non-member cannot view the messaging page', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)->get(route('projects.messaging.index', $project))
        ->assertForbidden();
});

test('member can view the messaging page and connected workspace', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => ProjectRole::Viewer->value]);
    MessagingIntegration::factory()->create(['project_id' => $project->id, 'name' => 'Acme Corp']);

    $this->actingAs($member)->get(route('projects.messaging.index', $project))
        ->assertOk()
        ->assertSee('Acme Corp')
        ->assertSee('Connected');
});

test('owner can send a message through the integration', function () {
    Http::fake([
        'https://slack.com/api/conversations.list*' => Http::response([
            'ok' => true,
            'channels' => [['id' => 'C111', 'name' => 'general']],
        ]),
        'https://slack.com/api/chat.postMessage' => Http::response(['ok' => true]),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $integration = MessagingIntegration::factory()->create(['project_id' => $project->id]);

    $this->actingAs($owner)->post(route('projects.messaging.send', [$project, $integration]), [
        'channel' => 'C111',
        'message' => 'Deploy complete',
    ])->assertRedirect()->assertSessionHas('status');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/chat.postMessage')
        && $request->data()['channel'] === 'C111'
        && $request->data()['text'] === 'Deploy complete');
});

test('failed message send surfaces the platform error', function () {
    Http::fake([
        'https://slack.com/api/chat.postMessage' => Http::response(['ok' => false, 'error' => 'channel_not_found']),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $integration = MessagingIntegration::factory()->create(['project_id' => $project->id]);

    $this->actingAs($owner)->post(route('projects.messaging.send', [$project, $integration]), [
        'channel' => 'C999',
        'message' => 'nope',
    ])->assertSessionHasErrors('message');
});

test('owner can disconnect the messaging integration', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $integration = MessagingIntegration::factory()->create(['project_id' => $project->id]);

    $this->actingAs($owner)->delete(route('projects.messaging.destroy', [$project, $integration]))
        ->assertRedirect(route('projects.messaging.index', $project));

    $this->assertDatabaseCount('messaging_integrations', 0);
});

test('viewer cannot send messages or disconnect', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    $integration = MessagingIntegration::factory()->create(['project_id' => $project->id]);

    $this->actingAs($viewer)->post(route('projects.messaging.send', [$project, $integration]), [
        'channel' => 'C111',
        'message' => 'nope',
    ])->assertForbidden();

    $this->actingAs($viewer)->delete(route('projects.messaging.destroy', [$project, $integration]))
        ->assertForbidden();

    $this->assertDatabaseCount('messaging_integrations', 1);
});

test('owner can connect, list channels, send, and disconnect via the API', function () {
    Http::fake([
        'https://slack.com/api/auth.test' => Http::response([
            'ok' => true,
            'team' => 'Acme Corp',
            'team_id' => 'T123',
            'user_id' => 'U123',
        ]),
        'https://slack.com/api/conversations.list*' => Http::response([
            'ok' => true,
            'channels' => [['id' => 'C111', 'name' => 'general']],
        ]),
        'https://slack.com/api/chat.postMessage' => Http::response(['ok' => true]),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.messaging.store', $project), [
        'platform' => 'slack',
        'bot_token' => 'xoxb-valid-token-value',
    ])->assertCreated()
        ->assertJsonPath('data.platform', 'slack')
        ->assertJsonPath('data.name', 'Acme Corp');

    $integration = $project->messagingIntegration;

    $this->actingAs($owner)->getJson(route('api.v1.projects.messaging.channels', [$project, $integration]))
        ->assertOk()
        ->assertJsonPath('data.0.id', 'C111');

    $this->actingAs($owner)->postJson(route('api.v1.projects.messaging.send', [$project, $integration]), [
        'channel' => 'C111',
        'message' => 'Deploy complete',
    ])->assertOk();

    $this->actingAs($owner)->deleteJson(route('api.v1.projects.messaging.destroy', [$project, $integration]))
        ->assertOk();

    $this->assertDatabaseCount('messaging_integrations', 0);
});

test('API returns null integration when nothing is connected', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->getJson(route('api.v1.projects.messaging.index', $project))
        ->assertOk()
        ->assertJsonPath('data', null);
});
