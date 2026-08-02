<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can change a member role via web PATCH', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'operator']);

    $response = $this->actingAs($owner)->patch(route('projects.members.update', [$project, $member]), [
        'role' => 'viewer',
    ]);

    $response->assertRedirect(route('projects.members.index', $project));
    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->id,
        'user_id' => $member->id,
        'role' => 'viewer',
    ]);
});

test('operator cannot change a member role via web PATCH', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => 'operator']);
    $project->members()->attach($member->id, ['role' => 'viewer']);

    $this->actingAs($operator)->patch(route('projects.members.update', [$project, $member]), [
        'role' => 'admin',
    ])->assertForbidden();
});

test('a member can view the team members page', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'viewer']);

    $this->actingAs($member)->get(route('projects.members.index', $project))
        ->assertOk();
});

test('a non-member cannot view the team members page', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)->get(route('projects.members.index', $project))
        ->assertForbidden();
});
