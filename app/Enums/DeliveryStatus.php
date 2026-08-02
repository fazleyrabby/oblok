<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Snoozed = 'snoozed';
    case Acknowledged = 'acknowledged';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::Failed], true);
    }

    public function isActionable(): bool
    {
        return ! $this->isTerminal();
    }
}
