<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can list project members via API', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'operator']);

    $response = $this->actingAs($owner)->getJson(route('api.v1.projects.members.index', $project));

    $response->assertOk()
        ->assertJsonPath('data.0.id', $member->id)
        ->assertJsonPath('data.0.role', 'operator')
        ->assertJsonStructure(['data' => [['id', 'name', 'email', 'role', 'added_at']]]);
});

test('a non-member cannot list project members via API', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)->getJson(route('api.v1.projects.members.index', $project))
        ->assertForbidden();
});

test('owner can add a member via API and response includes role and added_at', function () {
    $owner = User::factory()->create();
    $colleague = User::factory()->create(['email' => 'colleague@oblok.dev']);
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($owner)->postJson(route('api.v1.projects.members.store', $project), [
        'email' => 'colleague@oblok.dev',
        'role' => 'admin',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.role', 'admin')
        ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role', 'added_at']])
        ->assertJsonPath('data.added_at', fn ($value) => $value !== null);

    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->id,
        'user_id' => $colleague->id,
        'role' => 'admin',
    ]);
});

test('operator cannot add a member via API', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => 'operator']);

    $this->actingAs($operator)->postJson(route('api.v1.projects.members.store', $project), [
        'email' => 'colleague@oblok.dev',
        'role' => 'operator',
    ])->assertForbidden();
});

test('admin can update a member role via API', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($admin->id, ['role' => 'admin']);
    $project->members()->attach($member->id, ['role' => 'operator']);

    $response = $this->actingAs($admin)->patchJson(route('api.v1.projects.members.update', [$project, $member]), [
        'role' => 'viewer',
    ]);

    $response->assertOk()->assertJsonPath('data.role', 'viewer');

    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->id,
        'user_id' => $member->id,
        'role' => 'viewer',
    ]);
});

test('operator cannot update a member role via API', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => 'operator']);
    $project->members()->attach($member->id, ['role' => 'viewer']);

    $this->actingAs($operator)->patchJson(route('api.v1.projects.members.update', [$project, $member]), [
        'role' => 'admin',
    ])->assertForbidden();
});

test('updating a non-member returns 404 via API', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($admin->id, ['role' => 'admin']);

    $this->actingAs($admin)->patchJson(route('api.v1.projects.members.update', [$project, $stranger]), [
        'role' => 'viewer',
    ])->assertNotFound();
});

test('role update rejects invalid role via API', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($admin->id, ['role' => 'admin']);
    $project->members()->attach($member->id, ['role' => 'operator']);

    $this->actingAs($admin)->patchJson(route('api.v1.projects.members.update', [$project, $member]), [
        'role' => 'owner',
    ])->assertUnprocessable();
});

test('owner can remove a member via API', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'operator']);

    $this->actingAs($owner)->deleteJson(route('api.v1.projects.members.destroy', [$project, $member]))
        ->assertNoContent();

    $this->assertDatabaseMissing('project_members', [
        'project_id' => $project->id,
        'user_id' => $member->id,
    ]);
});

test('viewer cannot remove a member via API', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => 'viewer']);
    $project->members()->attach($member->id, ['role' => 'operator']);

    $this->actingAs($viewer)->deleteJson(route('api.v1.projects.members.destroy', [$project, $member]))
        ->assertForbidden();
});
