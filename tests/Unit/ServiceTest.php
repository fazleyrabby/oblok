<?php

use App\Models\HealthCheckResult;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('service auto generates uuid primary key', function () {
    $service = Service::factory()->create();

    expect($service->id)->toBeString()
        ->and(strlen($service->id))->toBe(36);
});

test('service belongs to a project', function () {
    $project = Project::factory()->create();
    $service = Service::factory()->create(['project_id' => $project->id]);

    expect($service->project->id)->toBe($project->id);
});

test('service healthy and failing scopes work', function () {
    $healthy = Service::factory()->create(['status' => 'healthy']);
    $failing = Service::factory()->failing()->create();

    $healthyServices = Service::healthy()->get();
    $failingServices = Service::failing()->get();

    expect($healthyServices->contains($healthy))->toBeTrue()
        ->and($healthyServices->contains($failing))->toBeFalse()
        ->and($failingServices->contains($failing))->toBeTrue();
});

test('service has many health check results', function () {
    $service = Service::factory()->create();
    $result = HealthCheckResult::factory()->create(['service_id' => $service->id]);

    expect($service->healthCheckResults)->toHaveCount(1)
        ->and($service->healthCheckResults->first()->id)->toBe($result->id);
});
