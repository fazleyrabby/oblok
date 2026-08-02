<?php

use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create a service under their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('projects.services.store', $project), [
        'name' => 'Stripe Webhook Listener',
        'type' => 'http',
        'target' => 'https://api.stripe.com/health',
        'check_interval' => 60,
        'timeout' => 5,
        'expected_status_code' => 200,
    ]);

    $service = Service::where('name', 'Stripe Webhook Listener')->first();

    expect($service)->not->toBeNull()
        ->and($service->project_id)->toBe($project->id);

    $response->assertRedirect(route('projects.services.show', [$project, $service]));
});

test('user cannot manage services of another users project', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $service = Service::factory()->create(['project_id' => $project->id]);

    $response = $this->actingAs($otherUser)->get(route('projects.services.show', [$project, $service]));
    $response->assertForbidden();

    $deleteResponse = $this->actingAs($otherUser)->delete(route('projects.services.destroy', [$project, $service]));
    $deleteResponse->assertForbidden();
});

test('user can soft delete a service', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $service = Service::factory()->create(['project_id' => $project->id]);

    $response = $this->actingAs($user)->delete(route('projects.services.destroy', [$project, $service]));

    $response->assertRedirect(route('projects.services.index', $project));
    expect(Service::find($service->id))->toBeNull();
});
