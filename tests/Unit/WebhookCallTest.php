<?php

use App\Models\Project;
use App\Models\WebhookCall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('webhook call belongs to a project', function () {
    $project = Project::factory()->create();
    $webhookCall = WebhookCall::factory()->create(['project_id' => $project->id]);

    expect($webhookCall->project->is($project))->toBeTrue();
});

test('webhook call casts captured payloads and headers to arrays', function () {
    $webhookCall = WebhookCall::factory()->create([
        'request_payload' => ['status' => 'successful', 'commit_hash' => 'abc'],
        'request_headers' => ['content-type' => ['application/json']],
        'response_payload' => ['deployment_id' => 'uuid'],
        'replay_count' => 2,
    ]);

    expect($webhookCall->request_payload)->toBeArray()
        ->and($webhookCall->request_headers)->toBeArray()
        ->and($webhookCall->response_payload)->toBeArray()
        ->and($webhookCall->replay_count)->toBe(2);
});

test('webhook call can be scoped by event', function () {
    $project = Project::factory()->create();
    WebhookCall::factory()->create(['project_id' => $project->id, 'event' => 'deployment']);
    WebhookCall::factory()->create(['project_id' => $project->id, 'event' => 'stripe']);

    expect($project->webhookCalls()->ofEvent('stripe')->count())->toBe(1);
});
