<?php

use App\Models\Conversation;
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
        'api.groq.com/openai/v1/chat/completions' => Http::response([
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

test('asking persists the exchange to chat history', function () {
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'The payment service is healthy.']],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->postJson(
        route('api.v1.projects.ai.assistant', $project),
        ['message' => 'Is the payment service healthy?']
    );

    $conversation = Conversation::query()->first();

    expect($conversation)->not->toBeNull();
    expect($conversation->project_id)->toBe($project->id);
    expect($conversation->user_id)->toBe($user->id);
    expect($conversation->messages()->count())->toBe(2);
    expect($conversation->messages()->get()[0]->role)->toBe('user');
    expect($conversation->messages()->get()[0]->content)->toBe('Is the payment service healthy?');
    expect($conversation->messages()->get()[1]->role)->toBe('assistant');
    expect($conversation->messages()->get()[1]->content)->toBe('The payment service is healthy.');
});

test('repeat questions reuse the same conversation', function () {
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'All good.']],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->postJson(
        route('api.v1.projects.ai.assistant', $project),
        ['message' => 'First question?']
    );
    $this->actingAs($user)->postJson(
        route('api.v1.projects.ai.assistant', $project),
        ['message' => 'Second question?']
    );

    expect(Conversation::query()->count())->toBe(1);
    expect(Conversation::query()->first()->messages()->count())->toBe(4);
});

test('chat page is preloaded with persisted history', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $conversation = Conversation::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    $conversation->messages()->create(['role' => 'user', 'content' => 'What is down?']);
    $conversation->messages()->create(['role' => 'assistant', 'content' => 'Nothing is down.']);

    $response = $this->actingAs($user)->get(route('projects.ai-assistant', $project));

    $response->assertOk();
    $response->assertSee('What is down?');
    $response->assertSee('Nothing is down.');
});

test('assistant endpoint returns 502 when the provider fails', function () {
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response('upstream error', 503),
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

test('web chat endpoint streams tokens as server-sent events', function () {
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response(
            "data: {\"choices\":[{\"delta\":{\"content\":\"Payment\"}}]}\n\n"
            ."data: {\"choices\":[{\"delta\":{\"content\":\" service\"}}]}\n\n"
            ."data: {\"choices\":[{\"delta\":{\"content\":\" healthy.\"}}]}\n\n"
            ."data: [DONE]\n\n",
            200,
            ['Content-Type' => 'text/event-stream']
        ),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(
        route('projects.ai-assistant.ask', $project),
        ['message' => 'Is the payment service healthy?']
    );

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/event-stream');

    $stream = $response->streamedContent();

    expect($stream)
        ->toContain('event: token')
        ->toContain('{"answer":"Payment"}')
        ->toContain('{"answer":" service"}')
        ->toContain('{"answer":" healthy."}')
        ->toContain('event: done');

    expect(strpos($stream, '{"answer":"Payment"}'))
        ->toBeLessThan(strpos($stream, '{"answer":" service"}'))
        ->toBeLessThan(strpos($stream, '{"answer":" healthy."}'));

    $conversation = Conversation::query()->first();
    expect($conversation->messages()->count())->toBe(2);
    expect($conversation->messages()->get()[1]->content)->toBe('Payment service healthy.');
});

test('web chat endpoint emits an error event when the provider fails', function () {
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response('upstream error', 503),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(
        route('projects.ai-assistant.ask', $project),
        ['message' => 'What is going on?']
    );

    $response->assertOk();
    $stream = $response->streamedContent();

    expect($stream)
        ->toContain('event: error')
        ->toContain('The AI assistant could not be reached.');
});

test('unauthenticated users cannot ask via the web endpoint', function () {
    $project = Project::factory()->create();

    $this->post(route('projects.ai-assistant.ask', $project), ['message' => 'hi'])
        ->assertRedirect(route('login'));
});

test('clear endpoint deletes the persisted chat history', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $conversation = Conversation::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    $conversation->messages()->create(['role' => 'user', 'content' => 'What is down?']);
    $conversation->messages()->create(['role' => 'assistant', 'content' => 'Nothing is down.']);

    $response = $this->actingAs($user)->post(route('projects.ai-assistant.clear', $project));

    $response->assertOk();
    expect($conversation->messages()->count())->toBe(0);
});

test('degenerate provider output is rejected on the non-streaming endpoint', function () {
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'We need to summarize the<unk><unk><unk>urp Zap urp Zap urp Zap']],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(
        route('api.v1.projects.ai.assistant', $project),
        ['message' => 'Summarize the project']
    );

    $response->assertStatus(502);
});
