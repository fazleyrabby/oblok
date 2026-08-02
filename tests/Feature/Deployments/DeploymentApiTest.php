<?php

use App\Models\Deployment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can list deployments for project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Deployment::factory()->count(3)->create(['project_id' => $project->id]);

    $response = $this->actingAs($user)->getJson(route('api.v1.projects.deployments.index', $project));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'project_id', 'environment', 'commit_hash', 'status', 'created_at'],
            ],
        ]);
});

test('user cannot view deployments for another users project', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($otherUser)->getJson(route('api.v1.projects.deployments.index', $project));

    $response->assertForbidden();
});
