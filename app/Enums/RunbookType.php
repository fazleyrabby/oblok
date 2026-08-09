<?php

namespace App\Enums;

enum RunbookType: string
{
    case Artisan = 'artisan';
    case Webhook = 'webhook';
    case Shell = 'shell';

    public function label(): string
    {
        return match ($this) {
            self::Artisan => 'Artisan Command',
            self::Webhook => 'HTTP Webhook',
            self::Shell => 'Shell Script',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Artisan => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            self::Webhook => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            self::Shell => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        };
    }
}
