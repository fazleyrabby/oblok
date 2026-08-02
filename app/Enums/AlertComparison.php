<?php

namespace App\Enums;

enum AlertComparison: string
{
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case Gt = 'gt';
    case Lt = 'lt';

    public function label(): string
    {
        return match ($this) {
            self::Equals => 'equals',
            self::NotEquals => 'does not equal',
            self::Gt => 'greater than',
            self::Lt => 'less than',
        };
    }

    public function matches(int|string $actual, int $threshold): bool
    {
        return match ($this) {
            self::Equals => $actual == $threshold,
            self::NotEquals => $actual != $threshold,
            self::Gt => (int) $actual > $threshold,
            self::Lt => (int) $actual < $threshold,
        };
    }
}
