<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('project owner can view the AI assistant page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('projects.ai-assistant', $project));

    $response->assertOk();
    $response->assertSee('AI Assistant');
});

test('non-members cannot view the AI assistant page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $response = $this->actingAs($user)->get(route('projects.ai-assistant', $project));

    $response->assertForbidden();
});

test('authenticated user can ask the assistant about their project', function () {
    Http::fake([
        'openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Your payment service is healthy.']],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(
        route('api.v1.projects.ai.assistant', $project),
        ['message' => 'Is the payment service healthy?']
    );

    $response->assertOk();
    $response->assertJsonPath('data.answer', 'Your payment service is healthy.');

    Http::assertSent(function ($request) {
        $payload = $request->data();
        $messages = $payload['messages'] ?? [];

        return count($messages) === 2
            && $messages[0]['role'] === 'system'
            && $messages[1]['role'] === 'user'
            && str_contains($messages[1]['content'], 'Is the payment service healthy?');
    });
});

test('assistant endpoint returns 502 when the provider fails', function () {
    Http::fake([
        'openrouter.ai/api/v1/chat/completions' => Http::response('upstream error', 503),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(
        route('api.v1.projects.ai.assistant', $project),
        ['message' => 'What is going on?']
    );

    $response->assertStatus(502);
});

test('message is required and limited to 2000 characters', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $missing = $this->actingAs($user)->postJson(
        route('api.v1.projects.ai.assistant', $project),
        []
    );
    $missing->assertStatus(422);

    $tooLong = $this->actingAs($user)->postJson(
        route('api.v1.projects.ai.assistant', $project),
        ['message' => str_repeat('a', 2001)]
    );
    $tooLong->assertStatus(422);
});

test('web chat endpoint answers via the authenticated session', function () {
    Http::fake([
        'openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'The payment service is healthy.']],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(
        route('projects.ai-assistant.ask', $project),
        ['message' => 'Is the payment service healthy?']
    );

    $response->assertOk();
    $response->assertJsonPath('data.answer', 'The payment service is healthy.');
});

test('web chat endpoint returns 502 when the provider fails', function () {
    Http::fake([
        'openrouter.ai/api/v1/chat/completions' => Http::response('upstream error', 503),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(
        route('projects.ai-assistant.ask', $project),
        ['message' => 'What is going on?']
    );

    $response->assertStatus(502);
});

test('unauthenticated users cannot ask via the web endpoint', function () {
    $project = Project::factory()->create();

    $this->post(route('projects.ai-assistant.ask', $project), ['message' => 'hi'])
        ->assertRedirect(route('login'));
});

test('degenerate provider output is rejected instead of surfaced', function () {
    Http::fake([
        'openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'We need to summarize the<unk><unk><unk>urp Zap urp Zap urp Zap']],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(
        route('projects.ai-assistant.ask', $project),
        ['message' => 'Summarize the project']
    );

    $response->assertStatus(502);
});
