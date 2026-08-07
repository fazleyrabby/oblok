<?php

namespace App\Models;

use App\Enums\AlertSeverity;
use Database\Factories\AlertEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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
 * @property string $state
 * @property string|null $fingerprint
 * @property Carbon $triggered_at
 * @property Carbon|null $resolved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['alert_rule_id', 'project_id', 'severity', 'subject', 'context', 'state', 'fingerprint', 'triggered_at', 'resolved_at'])]
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
            'resolved_at' => 'datetime',
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

    /**
     * Scope a query to only include firing (active) alert events.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFiring(Builder $query): Builder
    {
        return $query->where('state', 'firing');
    }

    /**
     * Scope a query to only include resolved alert events.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('state', 'resolved');
    }

    /**
     * Determine if the alert event is currently firing.
     */
    public function isFiring(): bool
    {
        return $this->state === 'firing';
    }

    /**
     * Resolve the alert event.
     */
    public function resolve(): bool
    {
        return $this->update([
            'state' => 'resolved',
            'resolved_at' => now(),
        ]);
    }
}
