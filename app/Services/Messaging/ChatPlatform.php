<?php

namespace App\Services\Messaging;

use App\Enums\MessagingPlatform;
use App\Services\Messaging\Data\ChatChannelData;
use App\Services\Messaging\Exceptions\MessagingApiException;

interface ChatPlatform
{
    /**
     * Validate the platform credentials and return the connection metadata.
     *
     * @param  array<string, mixed>  $credentials
     * @return array{name: string, config: array<string, mixed>}
     *
     * @throws MessagingApiException
     */
    public function verify(array $credentials): array;

    /**
     * List the channels the integration can post messages to.
     *
     * @param  array<string, mixed>  $config
     * @return array<int, ChatChannelData>
     *
     * @throws MessagingApiException
     */
    public function channels(array $config): array;

    /**
     * Send a message to the given channel.
     *
     * @param  array<string, mixed>  $config
     *
     * @throws MessagingApiException
     */
    public function send(array $config, string $channel, string $message): void;

    /**
     * The platform this driver implements.
     */
    public function platform(): MessagingPlatform;
}
