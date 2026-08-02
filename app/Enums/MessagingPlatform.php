<?php

namespace App\Enums;

enum MessagingPlatform: string
{
    case Slack = 'slack';

    /**
     * Human-readable label for the platform.
     */
    public function label(): string
    {
        return match ($this) {
            self::Slack => 'Slack',
        };
    }
}
