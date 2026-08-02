<?php

use App\Models\LogEntry;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can list logs for project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    LogEntry::factory()->count(5)->create(['project_id' => $project->id]);

    $response = $this->actingAs($user)->get(route('projects.logs.index', $project));

    $response->assertOk()
        ->assertSee('Log Stream');
});

test('user can ingest log entry via rest api', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(route('api.v1.projects.logs.store', $project), [
        'level' => 'error',
        'message' => 'Stripe webhook signature validation failed',
        'channel' => 'billing',
        'context' => ['signature' => 't=123,v1=456'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.level', 'error')
        ->assertJsonPath('data.message', 'Stripe webhook signature validation failed');

    $log = LogEntry::where('project_id', $project->id)->first();
    expect($log)->not->toBeNull()
        ->and($log->channel)->toBe('billing');
});

test('user cannot view logs for another users project', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($otherUser)->get(route('projects.logs.index', $project));

    $response->assertForbidden();
});
