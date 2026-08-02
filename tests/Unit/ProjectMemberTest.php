<?php

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('project members relationship attaches user with role pivot', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $project->members()->attach($member->id, ['role' => 'admin']);

    expect($project->members->contains($member))->toBeTrue()
        ->and($project->members->first()->pivot->role)->toBe(ProjectRole::Admin);
});
