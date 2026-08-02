<?php

namespace App\Services\Messaging;

use App\Enums\MessagingPlatform;

class MessagingDriverRegistry
{
    /**
     * @var array<string, class-string<ChatPlatform>>
     */
    private array $drivers = [];

    /**
     * Register a driver implementation for a messaging platform.
     */
    public function register(MessagingPlatform $platform, string $driverClass): void
    {
        $this->drivers[$platform->value] = $driverClass;
    }

    /**
     * Resolve the driver for the given platform.
     *
     * @throws \RuntimeException
     */
    public function for(MessagingPlatform $platform): ChatPlatform
    {
        $class = $this->drivers[$platform->value] ?? null;

        if (! $class) {
            throw new \RuntimeException("No messaging driver registered for [{$platform->value}].");
        }

        return app($class);
    }
}
