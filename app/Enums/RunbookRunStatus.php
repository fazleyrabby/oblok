<?php

namespace App\Enums;

enum RunbookRunStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Successful = 'successful';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Running => 'Running',
            self::Successful => 'Successful',
            self::Failed => 'Failed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'bg-gray-800 text-gray-400 border-gray-700',
            self::Running => 'bg-blue-500/10 text-blue-400 border-blue-500/20 animate-pulse',
            self::Successful => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            self::Failed => 'bg-red-500/10 text-red-400 border-red-500/20',
        };
    }
}
