<?php

use App\Models\Deployment;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('deployment generates uuid primary key', function () {
    $deployment = Deployment::factory()->create();

    expect($deployment->id)->toBeString()
        ->and(strlen($deployment->id))->toBe(36);
});

test('deployment belongs to a project', function () {
    $project = Project::factory()->create();
    $deployment = Deployment::factory()->create(['project_id' => $project->id]);

    expect($deployment->project->id)->toBe($project->id);
});

test('deployment status scopes work', function () {
    $successful = Deployment::factory()->create(['status' => 'successful']);
    $failed = Deployment::factory()->failed()->create();

    $successfulList = Deployment::successful()->get();
    $failedList = Deployment::failed()->get();

    expect($successfulList->contains($successful))->toBeTrue()
        ->and($successfulList->contains($failed))->toBeFalse()
        ->and($failedList->contains($failed))->toBeTrue();
});
