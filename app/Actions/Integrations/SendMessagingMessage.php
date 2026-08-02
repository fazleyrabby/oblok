<?php

namespace App\Actions\Integrations;

use App\Models\MessagingIntegration;
use App\Services\Messaging\Exceptions\MessagingApiException;
use App\Services\Messaging\MessagingDriverRegistry;

class SendMessagingMessage
{
    public function __construct(private readonly MessagingDriverRegistry $drivers) {}

    /**
     * Send a message to the integration's platform.
     *
     * @throws MessagingApiException When the platform rejects the message.
     */
    public function handle(MessagingIntegration $integration, string $channel, string $message): void
    {
        $driver = $this->drivers->for($integration->platform);

        $driver->send($integration->config, $channel, $message);
    }
}
