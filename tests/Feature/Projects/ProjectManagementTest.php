<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view projects list', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('projects.index'));

    $response->assertOk()
        ->assertSee($project->name);
});

test('user can create a project with auto-generated slug', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('projects.store'), [
        'name' => 'Atlas Monitoring System',
        'description' => 'Self-hosted monitoring backend',
    ]);

    $project = Project::where('name', 'Atlas Monitoring System')->first();

    expect($project)->not->toBeNull()
        ->and($project->slug)->toBe('atlas-monitoring-system')
        ->and($project->user_id)->toBe($user->id);

    $response->assertRedirect(route('projects.show', $project));
});

test('user can update their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

    $response = $this->actingAs($user)->put(route('projects.update', $project), [
        'name' => 'New Name Updated',
        'slug' => 'new-name-updated',
        'description' => 'Updated description text',
    ]);

    $response->assertRedirect(route('projects.show', $project));

    expect($project->fresh()->name)->toBe('New Name Updated');
});

test('user cannot view or update another users project', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($otherUser)->get(route('projects.show', $project));
    $response->assertForbidden();

    $updateResponse = $this->actingAs($otherUser)->put(route('projects.update', $project), [
        'name' => 'Hacked Name',
    ]);
    $updateResponse->assertForbidden();
});

test('user can archive and unarchive a project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('projects.archive', $project), ['archive' => '1']);
    expect($project->fresh()->isArchived())->toBeTrue();

    $this->actingAs($user)->post(route('projects.archive', $project), ['archive' => '0']);
    expect($project->fresh()->isArchived())->toBeFalse();
});

test('user can soft delete a project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('projects.destroy', $project));

    $response->assertRedirect(route('projects.index'));
    expect(Project::find($project->id))->toBeNull()
        ->and(Project::withTrashed()->find($project->id))->not->toBeNull();
});
