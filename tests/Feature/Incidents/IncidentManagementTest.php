<?php

use App\Events\ServiceStatusChanged;
use App\Models\Incident;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can log an operational incident for their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('projects.incidents.store', $project), [
        'title' => 'Stripe Webhook Gateway Timeout',
        'description' => 'Upstream gateway latency > 5000ms causing checkout failures.',
        'severity' => 'high',
    ]);

    $incident = Incident::where('title', 'Stripe Webhook Gateway Timeout')->first();

    expect($incident)->not->toBeNull()
        ->and($incident->project_id)->toBe($project->id);

    $response->assertRedirect(route('projects.incidents.show', [$project, $incident]));
});

test('user can resolve an open incident', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $incident = Incident::factory()->create([
        'project_id' => $project->id,
        'status' => 'investigating',
    ]);

    $response = $this->actingAs($user)->post(route('projects.incidents.resolve', [$project, $incident]));

    $response->assertRedirect(route('projects.incidents.show', [$project, $incident]));
    expect($incident->fresh()->isResolved())->toBeTrue();
});

test('service failure event automatically creates an incident', function () {
    $project = Project::factory()->create();
    $service = Service::factory()->create(['project_id' => $project->id, 'name' => 'Payment Gateway API']);

    event(new ServiceStatusChanged($service, 'healthy', 'failing'));

    $incident = Incident::where('service_id', $service->id)->first();

    expect($incident)->not->toBeNull()
        ->and($incident->title)->toContain('Service Failure Detected: Payment Gateway API')
        ->and($incident->severity)->toBe('high');
});
