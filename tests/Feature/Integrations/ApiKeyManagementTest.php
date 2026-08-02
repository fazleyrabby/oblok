<?php

use App\Enums\ProjectRole;
use App\Models\ApiKey;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can create an API key via web and sees the plaintext once', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.api-keys.store', $project), [
        'name' => 'CI deploy token',
    ])->assertRedirect(route('projects.api-keys.index', $project))
        ->assertSessionHas('createdApiKey');

    $token = session('createdApiKey');
    $this->assertDatabaseHas('api_keys', [
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'name' => 'CI deploy token',
    ]);

    $key = ApiKey::first();
    expect($key->getRawOriginal('token'))->toBe(hash('sha256', $token))
        ->and($key->getRawOriginal('token'))->not->toBe($token);
});

test('admin can create an API key', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($admin->id, ['role' => ProjectRole::Admin->value]);

    $this->actingAs($admin)->post(route('projects.api-keys.store', $project), [
        'name' => 'ops token',
    ])->assertRedirect()->assertSessionHas('createdApiKey');

    $this->assertDatabaseCount('api_keys', 1);
});

test('operator cannot create an API key', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);

    $this->actingAs($operator)->post(route('projects.api-keys.store', $project), [
        'name' => 'sneaky',
    ])->assertForbidden();

    $this->assertDatabaseCount('api_keys', 0);
});

test('non-member cannot view the API keys page', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)->get(route('projects.api-keys.index', $project))
        ->assertForbidden();
});

test('member can view API keys with prefix and usage summary', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    ApiKey::factory()->create(['project_id' => $project->id, 'name' => 'worker token']);

    $this->actingAs($viewer)->get(route('projects.api-keys.index', $project))
        ->assertOk()
        ->assertSee('worker token');
});

test('owner can revoke an API key', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $key = ApiKey::factory()->create(['project_id' => $project->id, 'user_id' => $owner->id]);

    $this->actingAs($owner)->delete(route('projects.api-keys.destroy', [$project, $key]))
        ->assertRedirect(route('projects.api-keys.index', $project));

    expect($key->fresh()->revoked_at)->not->toBeNull();
});

test('viewer cannot revoke an API key', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    $key = ApiKey::factory()->create(['project_id' => $project->id, 'user_id' => $owner->id]);

    $this->actingAs($viewer)->delete(route('projects.api-keys.destroy', [$project, $key]))
        ->assertForbidden();

    expect($key->fresh()->revoked_at)->toBeNull();
});

test('expiry must be a future date', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->post(route('projects.api-keys.store', $project), [
        'name' => 'expired by mistake',
        'expires_at' => now()->subDay()->toDateString(),
    ])->assertSessionHasErrors('expires_at');

    $this->assertDatabaseCount('api_keys', 0);
});
