<?php

namespace App\Enums;

enum TaskRunStatus: string
{
    case Running = 'running';
    case Success = 'success';
    case Failed = 'failed';
    case Missed = 'missed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Running => 'Running',
            self::Success => 'Success',
            self::Failed => 'Failed',
            self::Missed => 'Missed',
            self::Skipped => 'Skipped',
        };
    }

    /**
     * Tailwind classes for status badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Running => 'text-sky-400 border-sky-900 bg-sky-950',
            self::Success => 'text-emerald-400 border-emerald-900 bg-emerald-950',
            self::Failed => 'text-rose-400 border-rose-900 bg-rose-950',
            self::Missed => 'text-amber-400 border-amber-900 bg-amber-950',
            self::Skipped => 'text-gray-400 border-gray-800 bg-gray-900',
        };
    }

    public function isFailed(): bool
    {
        return in_array($this, [self::Failed, self::Missed], true);
    }
}
