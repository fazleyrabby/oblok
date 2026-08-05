<?php

use App\Models\Conversation;
use App\Models\Project;
use App\Models\User;
use App\Models\AiProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('project owner can view AI settings page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('projects.ai-settings.index', $project));

    $response->assertOk();
    $response->assertSee('AI Settings');
});

test('non-owner cannot view AI settings page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $response = $this->actingAs($user)->get(route('projects.ai-settings.index', $project));

    $response->assertForbidden();
});

test('project owner can store a custom AI provider', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('projects.ai-settings.store', $project), [
        'name' => 'My Llama.cpp',
        'endpoint' => 'http://192.168.0.222:8080/v1',
        'api_key' => 'secret-token',
        'models' => 'llama-3b, llama-8b',
        'timeout' => 45,
    ]);

    $response->assertRedirect(route('projects.ai-settings.index', $project));
    
    $provider = AiProvider::first();
    expect($provider)->not->toBeNull();
    expect($provider->name)->toBe('My Llama.cpp');
    expect($provider->endpoint)->toBe('http://192.168.0.222:8080/v1');
    expect($provider->api_key)->toBe('secret-token');
    expect($provider->models)->toBe(['llama-3b', 'llama-8b']);
    expect($provider->timeout)->toBe(45);
});

test('project owner can delete an AI provider', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $provider = AiProvider::create([
        'project_id' => $project->id,
        'name' => 'Delete Me',
        'endpoint' => 'http://localhost/v1',
        'models' => ['delete-model'],
        'timeout' => 60,
    ]);

    $response = $this->actingAs($user)->delete(route('projects.ai-settings.destroy', [$project, $provider]));

    $response->assertRedirect(route('projects.ai-settings.index', $project));
    expect(AiProvider::count())->toBe(0);
});

test('can select model for conversation', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $provider = AiProvider::create([
        'project_id' => $project->id,
        'name' => 'Custom Provider',
        'endpoint' => 'http://localhost/v1',
        'models' => ['custom-model'],
        'timeout' => 60,
    ]);

    $response = $this->actingAs($user)->postJson(route('projects.ai-assistant.select-model', $project), [
        'provider_id' => $provider->id,
        'model' => 'custom-model',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.success', true);

    $conversation = Conversation::first();
    expect($conversation->selected_provider_id)->toBe($provider->id);
    expect($conversation->selected_model)->toBe('custom-model');
});

test('assistant streams tokens from the custom provider when selected', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $provider = AiProvider::create([
        'project_id' => $project->id,
        'name' => 'Custom Provider',
        'endpoint' => 'http://localhost/v1',
        'api_key' => 'custom-secret-key',
        'models' => ['custom-model'],
        'timeout' => 60,
    ]);

    $conversation = Conversation::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'selected_provider_id' => $provider->id,
        'selected_model' => 'custom-model',
    ]);

    Http::fake([
        'localhost/v1/chat/completions' => Http::response(
            "data: {\"choices\":[{\"delta\":{\"content\":\"Custom response\"}}]}\n\n"
            ."data: [DONE]\n\n",
            200,
            ['Content-Type' => 'text/event-stream']
        ),
    ]);

    $response = $this->actingAs($user)->post(
        route('projects.ai-assistant.ask', $project),
        ['message' => 'Hello custom AI']
    );

    $response->assertOk();
    expect($response->streamedContent())->toContain('Custom response');

    Http::assertSent(function ($request) use ($provider) {
        return $request->url() === 'http://localhost/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer custom-secret-key')
            && $request['model'] === 'custom-model';
    });
});
