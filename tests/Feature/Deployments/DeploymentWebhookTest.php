<?php

use App\Models\Deployment;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public webhook endpoint receives and processes deployment payload', function () {
    $project = Project::factory()->create(['slug' => 'atlas-core-backend']);

    $response = $this->postJson(route('api.v1.webhooks.deployments', $project->slug), [
        'environment' => 'production',
        'commit_hash' => 'a40c443b4c8023b8ea1a899cafb4856caa35cafc',
        'commit_message' => 'feat(services): implement phase 4 service health monitoring',
        'author' => 'Fazley Rabbi',
        'status' => 'successful',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.environment', 'production')
        ->assertJsonPath('data.author', 'Fazley Rabbi');

    $deployment = Deployment::where('project_id', $project->id)->first();

    expect($deployment)->not->toBeNull()
        ->and($deployment->commit_hash)->toBe('a40c443b4c8023b8ea1a899cafb4856caa35cafc');
});
