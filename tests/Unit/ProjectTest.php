<?php

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it auto-generates a UUID primary key', function () {
    $project = Project::factory()->create();

    expect($project->id)->toBeString()
        ->and(strlen($project->id))->toBe(36);
});

test('it casts metadata to an array', function () {
    $project = Project::factory()->create([
        'metadata' => ['environment' => 'production', 'version' => '1.0'],
    ]);

    expect($project->metadata)->toBeArray()
        ->and($project->metadata['environment'])->toBe('production');
});

test('it supports active and archived scopes', function () {
    $active = Project::factory()->create(['archived_at' => null]);
    $archived = Project::factory()->archived()->create();

    $activeProjects = Project::active()->get();
    $archivedProjects = Project::archived()->get();

    expect($activeProjects->contains($active))->toBeTrue()
        ->and($activeProjects->contains($archived))->toBeFalse()
        ->and($archivedProjects->contains($archived))->toBeTrue();
});

test('it handles archiving and unarchiving domain helpers', function () {
    $project = Project::factory()->create(['archived_at' => null]);

    expect($project->isArchived())->toBeFalse();

    $project->archive();
    expect($project->isArchived())->toBeTrue();

    $project->unarchive();
    expect($project->isArchived())->toBeFalse();
});
