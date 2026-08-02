<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project owner can add team member by email', function () {
    $owner = User::factory()->create();
    $colleague = User::factory()->create(['email' => 'colleague@atlas.dev']);
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.members.store', $project), [
        'email' => 'colleague@atlas.dev',
        'role' => 'admin',
    ]);

    $response->assertRedirect(route('projects.members.index', $project));
    expect($project->members()->where('user_id', $colleague->id)->exists())->toBeTrue();
});

test('project owner can remove team member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'operator']);

    $response = $this->actingAs($owner)->delete(route('projects.members.destroy', [$project, $member]));

    $response->assertRedirect(route('projects.members.index', $project));
    expect($project->members()->where('user_id', $member->id)->exists())->toBeFalse();
});
