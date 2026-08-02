<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('api returns paginated list of user projects', function () {
    $user = User::factory()->create();
    Project::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson(route('api.v1.projects.index'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'user_id', 'name', 'slug', 'description', 'metadata', 'is_archived', 'created_at', 'updated_at'],
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ]);
});

test('api creates project with json envelope', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('api.v1.projects.store'), [
        'name' => 'API Microservice Project',
        'metadata' => ['environment' => 'production'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'API Microservice Project')
        ->assertJsonPath('data.metadata.environment', 'production');
});

test('api supports search filter', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->id, 'name' => 'Target Project']);
    Project::factory()->create(['user_id' => $user->id, 'name' => 'Other App']);

    $response = $this->actingAs($user)->getJson(route('api.v1.projects.index', ['search' => 'Target']));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Target Project');
});
