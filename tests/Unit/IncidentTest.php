<?php

use App\Models\Incident;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('incident generates uuid primary key', function () {
    $incident = Incident::factory()->create();

    expect($incident->id)->toBeString()
        ->and(strlen($incident->id))->toBe(36);
});

test('incident belongs to project and optional service', function () {
    $project = Project::factory()->create();
    $service = Service::factory()->create(['project_id' => $project->id]);
    $incident = Incident::factory()->create([
        'project_id' => $project->id,
        'service_id' => $service->id,
    ]);

    expect($incident->project->id)->toBe($project->id)
        ->and($incident->service->id)->toBe($service->id);
});

test('incident resolve method updates status and sets timestamp', function () {
    $incident = Incident::factory()->create(['status' => 'investigating']);

    expect($incident->isResolved())->toBeFalse();

    $incident->resolve();

    expect($incident->isResolved())->toBeTrue()
        ->and($incident->resolved_at)->not->toBeNull();
});
