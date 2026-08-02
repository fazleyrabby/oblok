<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Info => 'Info',
            self::Warning => 'Warning',
            self::Critical => 'Critical',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Info => 'text-blue-400 bg-blue-500/10 border-blue-500/20',
            self::Warning => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
            self::Critical => 'text-red-400 bg-red-500/10 border-red-500/20',
        };
    }
}
