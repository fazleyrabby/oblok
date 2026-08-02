<?php

use App\Enums\ProjectRole;
use App\Models\ApiKey;
use App\Models\Project;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makeKey(User $user, Project $project, string $name = 'worker', ?array $overrides = []): array
{
    $token = 'atl_'.Str::random(36);

    $key = ApiKey::factory()->create(array_merge([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'name' => $name,
        'token' => hash('sha256', $token),
        'key_prefix' => substr($token, 0, 12),
    ], $overrides));

    return [$key, $token];
}

test('bearer token authenticates log ingestion and records usage', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    [, $token] = makeKey($owner, $project);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson(route('api.v1.projects.logs.store', $project), [
            'message' => 'Order processed',
            'level' => 'info',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('logs', [
        'project_id' => $project->id,
        'message' => 'Order processed',
    ]);

    expect(ApiKey::first()->requests_count)->toBe(1)
        ->and(ApiKey::first()->last_used_at)->not->toBeNull();
});

test('missing token is rejected with 401', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->postJson(route('api.v1.projects.logs.store', $project), [
        'message' => 'nope',
    ])->assertUnauthorized();
});

test('invalid token is rejected with 401', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->withHeaders(['Authorization' => 'Bearer atl_not-a-real-token'])
        ->postJson(route('api.v1.projects.logs.store', $project), [
            'message' => 'nope',
        ])->assertUnauthorized();
});

test('revoked key is rejected with 401', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    [$key, $token] = makeKey($owner, $project);
    $key->forceFill(['revoked_at' => now()])->save();

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson(route('api.v1.projects.logs.store', $project), [
            'message' => 'nope',
        ])->assertUnauthorized();
});

test('expired key is rejected with 401', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    [, $token] = makeKey($owner, $project, 'worker', ['expires_at' => now()->subDay()]);

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson(route('api.v1.projects.logs.store', $project), [
            'message' => 'nope',
        ])->assertUnauthorized();
});

test('session authentication still works on the API', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.logs.store', $project), [
        'message' => 'session based',
    ])->assertCreated();
});

test('key scoped to one project is rejected for another project', function () {
    $owner = User::factory()->create();
    $projectA = Project::factory()->create(['user_id' => $owner->id]);
    $projectB = Project::factory()->create(['user_id' => $owner->id]);
    [, $token] = makeKey($owner, $projectA);

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson(route('api.v1.projects.logs.store', $projectB), [
            'message' => 'wrong project',
        ])->assertForbidden();
});

test('operator key can ingest logs but viewer key cannot', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);

    [, $operatorToken] = makeKey($operator, $project, 'operator');
    [, $viewerToken] = makeKey($viewer, $project, 'viewer');

    $this->withHeaders(['Authorization' => "Bearer {$operatorToken}"])
        ->postJson(route('api.v1.projects.logs.store', $project), [
            'message' => 'allowed',
        ])->assertCreated();

    $this->withHeaders(['Authorization' => "Bearer {$viewerToken}"])
        ->postJson(route('api.v1.projects.logs.store', $project), [
            'message' => 'denied',
        ])->assertForbidden();
});

test('API keys can be created, listed, and revoked via the API', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.api-keys.store', $project), [
        'name' => 'worker token',
    ])->assertCreated()
        ->assertJsonPath('data.name', 'worker token')
        ->assertJsonStructure(['token']);

    $this->actingAs($owner)->getJson(route('api.v1.projects.api-keys.index', $project))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'worker token');

    $key = ApiKey::first();

    $this->actingAs($owner)->deleteJson(route('api.v1.projects.api-keys.destroy', [$project, $key]))
        ->assertOk();

    expect($key->fresh()->revoked_at)->not->toBeNull();
});

test('rate limiting rejects requests beyond the per-key limit', function () {
    RateLimiter::for('api_key', fn ($request) => Limit::perMinute(3)->by($request->bearerToken()));

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    [, $token] = makeKey($owner, $project);

    for ($i = 0; $i < 3; $i++) {
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson(route('api.v1.projects.api-keys.index', $project))
            ->assertOk();
    }

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson(route('api.v1.projects.api-keys.index', $project))
        ->assertStatus(429);
});
