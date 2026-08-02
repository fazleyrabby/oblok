<?php

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookCall;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deployment webhook receiver captures a webhook call with payload and headers', function () {
    $project = Project::factory()->create(['slug' => 'oblok-core-backend']);

    $response = $this->postJson(route('api.v1.webhooks.deployments', $project->slug), [
        'environment' => 'production',
        'commit_hash' => 'a40c443b4c8023b8ea1a899cafb4856caa35cafc',
        'status' => 'successful',
    ], [
        'User-Agent' => 'GitHub-Hookshot/1.0',
    ]);

    $response->assertCreated();

    $webhookCall = WebhookCall::where('project_id', $project->id)->first();

    expect($webhookCall)->not->toBeNull()
        ->and($webhookCall->event)->toBe('deployment')
        ->and($webhookCall->method)->toBe('POST')
        ->and($webhookCall->status_code)->toBe(201)
        ->and($webhookCall->request_payload['commit_hash'])->toBe('a40c443b4c8023b8ea1a899cafb4856caa35cafc')
        ->and($webhookCall->request_headers['user-agent'][0])->toBe('GitHub-Hookshot/1.0')
        ->and($webhookCall->processing_time_ms)->not->toBeNull()
        ->and($webhookCall->response_payload['deployment_id'])->not->toBeNull();
});

test('owner can list and inspect webhook calls via web and API', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    WebhookCall::factory()->count(3)->create(['project_id' => $project->id]);

    $this->actingAs($owner)->get(route('projects.webhooks.index', $project))
        ->assertOk()
        ->assertSee('Webhooks');

    $webhookCall = WebhookCall::where('project_id', $project->id)->first();

    $this->actingAs($owner)->get(route('projects.webhooks.show', [$project, $webhookCall]))
        ->assertOk()
        ->assertSee('Request Payload');

    $this->actingAs($owner)->getJson(route('api.v1.projects.webhooks.index', $project))
        ->assertOk()
        ->assertJsonCount(3, 'data');

    $this->actingAs($owner)->getJson(route('api.v1.projects.webhooks.show', [$project, $webhookCall]))
        ->assertOk()
        ->assertJsonPath('data.id', $webhookCall->id)
        ->assertJsonStructure(['data' => ['request_payload', 'request_headers']]);
});

test('any project member can view webhook calls', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    WebhookCall::factory()->create(['project_id' => $project->id]);

    $this->actingAs($viewer)->get(route('projects.webhooks.index', $project))
        ->assertOk();
});

test('non-member cannot view webhook calls', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    WebhookCall::factory()->create(['project_id' => $project->id]);

    $this->actingAs($stranger)->get(route('projects.webhooks.index', $project))
        ->assertForbidden();
});

test('owner can replay a deployment webhook which creates a new deployment', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $webhookCall = WebhookCall::factory()->create([
        'project_id' => $project->id,
        'event' => 'deployment',
        'request_payload' => [
            'environment' => 'staging',
            'commit_hash' => 'abc123',
            'status' => 'successful',
        ],
    ]);

    $this->actingAs($owner)->post(route('projects.webhooks.replay', [$project, $webhookCall]))
        ->assertRedirect();

    expect($webhookCall->fresh()->replay_count)->toBe(1)
        ->and($webhookCall->fresh()->replayed_at)->not->toBeNull();

    $this->assertDatabaseHas('deployments', [
        'project_id' => $project->id,
        'environment' => 'staging',
        'commit_hash' => 'abc123',
    ]);
});

test('viewer cannot replay a webhook call', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    $webhookCall = WebhookCall::factory()->create([
        'project_id' => $project->id,
        'event' => 'deployment',
    ]);

    $this->actingAs($viewer)->post(route('projects.webhooks.replay', [$project, $webhookCall]))
        ->assertForbidden();

    expect($webhookCall->fresh()->replay_count)->toBe(0);
    $this->assertDatabaseCount('deployments', 0);
});

test('operator can replay a webhook via API', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);
    $webhookCall = WebhookCall::factory()->create([
        'project_id' => $project->id,
        'event' => 'deployment',
        'request_payload' => [
            'environment' => 'production',
            'commit_hash' => 'def456',
            'status' => 'successful',
        ],
    ]);

    $response = $this->actingAs($operator)->postJson(route('api.v1.projects.webhooks.replay', [$project, $webhookCall]));

    $response->assertOk()
        ->assertJsonPath('data.replay_count', 1);

    $this->assertDatabaseHas('deployments', [
        'project_id' => $project->id,
        'commit_hash' => 'def456',
    ]);
});

test('replay of an unsupported webhook event fails', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $webhookCall = WebhookCall::factory()->create([
        'project_id' => $project->id,
        'event' => 'stripe',
        'request_payload' => ['id' => 'evt_123'],
    ]);

    $this->actingAs($owner)->post(route('projects.webhooks.replay', [$project, $webhookCall]))
        ->assertRedirect();

    $this->assertDatabaseCount('deployments', 0);
});
