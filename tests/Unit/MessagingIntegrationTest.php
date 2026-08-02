<?php

use App\Actions\Integrations\ConnectMessagingIntegration;
use App\Actions\Integrations\DisconnectMessagingIntegration;
use App\Actions\Integrations\SendMessagingMessage;
use App\Enums\MessagingPlatform;
use App\Models\MessagingIntegration;
use App\Models\Project;
use App\Services\Messaging\Drivers\SlackDriver;
use App\Services\Messaging\Exceptions\MessagingApiException;
use App\Services\Messaging\MessagingDriverRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('driver registry resolves the slack driver and rejects unknown platforms', function () {
    $registry = app(MessagingDriverRegistry::class);

    expect($registry->for(MessagingPlatform::Slack))->toBeInstanceOf(SlackDriver::class);
});

test('slack driver verifies the bot token and captures workspace metadata', function () {
    Http::fake([
        'https://slack.com/api/auth.test' => Http::response([
            'ok' => true,
            'team' => 'Acme Corp',
            'team_id' => 'T1234567890',
            'user_id' => 'U1234567890',
        ]),
    ]);

    $result = app(SlackDriver::class)->verify(['bot_token' => 'xoxb-test-token']);

    expect($result['name'])->toBe('Acme Corp')
        ->and($result['config']['bot_user_id'])->toBe('U1234567890')
        ->and($result['config']['team_id'])->toBe('T1234567890');

    Http::assertSent(fn ($request) => $request->url() === 'https://slack.com/api/auth.test'
        && $request->hasHeader('Authorization', 'Bearer xoxb-test-token'));
});

test('slack driver propagates invalid credentials as a domain exception', function () {
    Http::fake([
        'https://slack.com/api/auth.test' => Http::response(['ok' => false, 'error' => 'invalid_auth']),
    ]);

    expect(fn () => app(SlackDriver::class)->verify(['bot_token' => 'xoxb-bad-token']))
        ->toThrow(MessagingApiException::class);
});

test('slack driver lists channels from the conversations api', function () {
    Http::fake([
        'https://slack.com/api/conversations.list*' => Http::response([
            'ok' => true,
            'channels' => [
                ['id' => 'C111', 'name' => 'general'],
                ['id' => 'C222', 'name' => 'incidents'],
            ],
        ]),
    ]);

    $channels = app(SlackDriver::class)->channels(['bot_token' => 'xoxb-test-token']);

    expect($channels)->toHaveCount(2)
        ->and($channels[0]->id)->toBe('C111')
        ->and($channels[0]->name)->toBe('general')
        ->and($channels[1]->name)->toBe('incidents');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/conversations.list'));
});

test('slack driver sends a message via chat.postMessage', function () {
    Http::fake([
        'https://slack.com/api/chat.postMessage' => Http::response(['ok' => true]),
    ]);

    app(SlackDriver::class)->send(['bot_token' => 'xoxb-test-token'], 'C111', 'Hello Atlas');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/chat.postMessage')
        && $request->data()['channel'] === 'C111'
        && $request->data()['text'] === 'Hello Atlas');
});

test('integration encrypts the platform config at rest', function () {
    $integration = MessagingIntegration::factory()->create([
        'config' => ['bot_token' => 'xoxb-secret-token'],
    ]);

    expect($integration->getRawOriginal('config'))->not->toContain('xoxb-secret-token')
        ->and($integration->config['bot_token'])->toBe('xoxb-secret-token');
});

test('integration exposes platform and project relationships and scopes', function () {
    $project = Project::factory()->create();
    $integration = MessagingIntegration::factory()->create([
        'project_id' => $project->id,
        'platform' => MessagingPlatform::Slack,
    ]);

    expect($integration->project->is($project))->toBeTrue()
        ->and($integration->platform)->toBe(MessagingPlatform::Slack)
        ->and($project->messagingIntegration->is($integration))->toBeTrue()
        ->and(MessagingIntegration::platform(MessagingPlatform::Slack)->count())->toBe(1);
});

test('connect action verifies credentials and stores them encrypted', function () {
    Http::fake([
        'https://slack.com/api/auth.test' => Http::response([
            'ok' => true,
            'team' => 'Acme Corp',
            'team_id' => 'T123',
            'user_id' => 'U123',
        ]),
    ]);

    $project = Project::factory()->create();

    $integration = app(ConnectMessagingIntegration::class)->handle(
        $project,
        MessagingPlatform::Slack,
        ['bot_token' => 'xoxb-new-token'],
        'C111'
    );

    expect($integration->name)->toBe('Acme Corp')
        ->and($integration->channel)->toBe('C111')
        ->and($integration->enabled)->toBeTrue()
        ->and($integration->last_connected_at)->not->toBeNull()
        ->and($integration->getRawOriginal('config'))->not->toContain('xoxb-new-token');
});

test('send action posts a message and disconnect deletes the integration', function () {
    Http::fake([
        'https://slack.com/api/chat.postMessage' => Http::response(['ok' => true]),
    ]);

    $integration = MessagingIntegration::factory()->create();

    app(SendMessagingMessage::class)->handle($integration, 'C111', 'Deploy complete');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/chat.postMessage'));

    app(DisconnectMessagingIntegration::class)->handle($integration);

    expect(MessagingIntegration::find($integration->id))->toBeNull();
});
