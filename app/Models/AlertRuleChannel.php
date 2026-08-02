<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property string $alert_rule_id
 * @property string $notification_channel_id
 * @property array<string, mixed>|null $recipient_filter
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AlertRuleChannel extends Pivot
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recipient_filter' => 'array',
        ];
    }
}
