<?php

namespace App\Models;

use App\Enums\AlertSeverity;
use Database\Factories\AlertEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $alert_rule_id
 * @property string $project_id
 * @property AlertSeverity $severity
 * @property string $subject
 * @property array<string, mixed>|null $context
 * @property Carbon $triggered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['alert_rule_id', 'project_id', 'severity', 'subject', 'context', 'triggered_at'])]
class AlertEvent extends Model
{
    /** @use HasFactory<AlertEventFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity' => AlertSeverity::class,
            'context' => 'array',
            'triggered_at' => 'datetime',
        ];
    }

    /**
     * Get the alert rule that triggered this event.
     *
     * @return BelongsTo<AlertRule, $this>
     */
    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class);
    }

    /**
     * Get the project this event belongs to.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the notification deliveries created for this event.
     *
     * @return HasMany<NotificationDelivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }
}
