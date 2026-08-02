<?php

namespace App\Services\Messaging\Drivers;

use App\Enums\MessagingPlatform;
use App\Services\Messaging\ChatPlatform;
use App\Services\Messaging\Data\ChatChannelData;
use App\Services\Messaging\Exceptions\MessagingApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class SlackDriver implements ChatPlatform
{
    /**
     * The platform this driver implements.
     */
    public function platform(): MessagingPlatform
    {
        return MessagingPlatform::Slack;
    }

    /**
     * Validate the bot token and capture the workspace metadata.
     *
     * @param  array<string, mixed>  $credentials
     * @return array{name: string, config: array<string, mixed>}
     */
    public function verify(array $credentials): array
    {
        $token = (string) ($credentials['bot_token'] ?? '');

        $data = $this->request('auth.test', 'post', $token);

        $workspaceName = is_string($data['team'] ?? null) ? $data['team'] : 'Slack workspace';

        return [
            'name' => $workspaceName,
            'config' => [
                'bot_token' => $token,
                'bot_user_id' => is_string($data['user_id'] ?? null) ? $data['user_id'] : null,
                'team_id' => is_string($data['team_id'] ?? null) ? $data['team_id'] : null,
            ],
        ];
    }

    /**
     * List the public and private channels the bot has joined or can join.
     *
     * @param  array<string, mixed>  $config
     * @return array<int, ChatChannelData>
     */
    public function channels(array $config): array
    {
        $data = $this->request('conversations.list', 'get', (string) $config['bot_token'], [
            'types' => 'public_channel,private_channel',
            'limit' => 200,
        ]);

        $channels = $data['channels'] ?? [];

        if (! is_array($channels)) {
            return [];
        }

        return array_map(
            static fn (array $channel) => new ChatChannelData(
                id: (string) ($channel['id'] ?? ''),
                name: (string) ($channel['name'] ?? ''),
            ),
            array_values($channels)
        );
    }

    /**
     * Post a message to the given Slack channel.
     *
     * @param  array<string, mixed>  $config
     */
    public function send(array $config, string $channel, string $message): void
    {
        $this->request('chat.postMessage', 'post', (string) $config['bot_token'], [
            'channel' => $channel,
            'text' => $message,
        ]);
    }

    /**
     * Perform a Slack Web API request, throwing a domain exception on failure.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     *
     * @throws MessagingApiException
     */
    protected function request(string $endpoint, string $method, string $token, array $query = []): array
    {
        try {
            $response = $this->client($token)->{$method}($endpoint, $query);

            $data = $response->json();

            if (! is_array($data) || ($data['ok'] ?? false) !== true) {
                $error = is_string($data['error'] ?? null) ? $data['error'] : 'HTTP '.$response->status();
                throw new MessagingApiException("Slack API error: {$error}");
            }

            return $data;
        } catch (ConnectionException|RequestException $e) {
            throw new MessagingApiException('Slack API request failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Build a Slack Web API client for the given bot token.
     */
    protected function client(string $token): PendingRequest
    {
        return Http::baseUrl((string) config('atlas.messaging.slack.api_url'))
            ->withToken($token)
            ->asJson()
            ->timeout((int) config('atlas.messaging.slack.timeout', 10));
    }
}
