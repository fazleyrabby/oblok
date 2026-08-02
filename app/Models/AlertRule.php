<?php

namespace App\Models;

use App\Enums\AlertComparison;
use App\Enums\AlertMetric;
use App\Enums\AlertSeverity;
use App\Support\Alerts\MetricReading;
use Database\Factories\AlertRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $name
 * @property string|null $description
 * @property AlertMetric $metric
 * @property AlertComparison $comparison
 * @property int|null $threshold
 * @property int|null $consecutive_failures
 * @property int $window_minutes
 * @property AlertSeverity $severity
 * @property bool $enabled
 * @property Carbon|null $last_evaluated_at
 * @property Carbon|null $last_triggered_at
 * @property int $cooldown_minutes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'project_id', 'name', 'description', 'metric', 'comparison', 'threshold',
    'consecutive_failures', 'window_minutes', 'severity', 'enabled',
    'last_evaluated_at', 'last_triggered_at', 'cooldown_minutes',
])]
class AlertRule extends Model
{
    /** @use HasFactory<AlertRuleFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metric' => AlertMetric::class,
            'comparison' => AlertComparison::class,
            'threshold' => 'integer',
            'consecutive_failures' => 'integer',
            'window_minutes' => 'integer',
            'severity' => AlertSeverity::class,
            'enabled' => 'boolean',
            'last_evaluated_at' => 'datetime',
            'last_triggered_at' => 'datetime',
            'cooldown_minutes' => 'integer',
        ];
    }

    /**
     * Get the project that owns the rule.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the notification channels attached to this rule.
     *
     * @return BelongsToMany<NotificationChannel, $this, AlertRuleChannel, 'pivot'>
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(NotificationChannel::class, 'alert_rule_channel')
            ->using(AlertRuleChannel::class)
            ->withPivot('recipient_filter')
            ->withTimestamps();
    }

    /**
     * Get the alert events triggered by this rule.
     *
     * @return HasMany<AlertEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(AlertEvent::class);
    }

    /**
     * Get the notification deliveries created by this rule.
     *
     * @return HasMany<NotificationDelivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    /**
     * Scope a query to only include enabled rules.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope a query to rules that are due for evaluation.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeDueForEvaluation(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereNull('last_evaluated_at')
            ->orWhere('last_evaluated_at', '<=', now()->subMinutes(1)));
    }

    /**
     * Determine whether the rule is in its cooldown window.
     */
    public function isInCooldown(): bool
    {
        if (! $this->last_triggered_at) {
            return false;
        }

        return $this->last_triggered_at->copy()->addMinutes($this->cooldown_minutes)->isFuture();
    }

    /**
     * Evaluate a metric reading against this rule.
     */
    public function evaluate(MetricReading $reading): bool
    {
        $value = match ($this->metric) {
            AlertMetric::ServiceHealth => (int) $reading->value,
            AlertMetric::QueueBacklog => (int) $reading->value,
            AlertMetric::DeploymentStatus => (int) $reading->value,
            AlertMetric::IncidentOpened => (int) $reading->value,
        };

        $threshold = match ($this->metric) {
            AlertMetric::ServiceHealth => (int) ($this->consecutive_failures ?? 1),
            AlertMetric::QueueBacklog,
            AlertMetric::DeploymentStatus,
            AlertMetric::IncidentOpened => (int) ($this->threshold ?? 1),
        };

        return $this->comparison->matches($value, $threshold);
    }
}
