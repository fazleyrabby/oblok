<?php

use App\Models\Incident;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('authorized user can get AI suggestions for an incident', function () {
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => "Root cause hypothesis:\nThe upstream timeout suggests saturation.\n\nSuggested next steps:\n- Check CPU\n- Scale workers"]],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $service = Service::factory()->create(['project_id' => $project->id]);
    $incident = Incident::factory()->create([
        'project_id' => $project->id,
        'service_id' => $service->id,
        'status' => 'investigating',
    ]);

    $response = $this->actingAs($user)->postJson(
        route('projects.incidents.suggest', [$project, $incident])
    );

    $response->assertOk();
    $response->assertJsonPath('data.suggestion', fn ($value) => str_contains($value, 'Root cause hypothesis'));
});

test('non-members cannot get incident suggestions', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $incident = Incident::factory()->create(['project_id' => $project->id]);

    $response = $this->actingAs($user)->postJson(
        route('projects.incidents.suggest', [$project, $incident])
    );

    $response->assertForbidden();
});

test('incident suggestion returns 502 when provider fails', function () {
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response('upstream error', 503),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $incident = Incident::factory()->create(['project_id' => $project->id]);

    $response = $this->actingAs($user)->postJson(
        route('projects.incidents.suggest', [$project, $incident])
    );

    $response->assertStatus(502);
});
