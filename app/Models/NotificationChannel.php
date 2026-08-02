<?php

namespace App\Models;

use App\Enums\NotificationChannelType;
use Database\Factories\NotificationChannelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $name
 * @property NotificationChannelType $type
 * @property array<string, mixed>|null $encrypted_config
 * @property bool $enabled
 * @property-read AlertRuleChannel|null $pivot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'name', 'type', 'encrypted_config', 'enabled'])]
class NotificationChannel extends Model
{
    /** @use HasFactory<NotificationChannelFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => NotificationChannelType::class,
            'encrypted_config' => 'encrypted:array',
            'enabled' => 'boolean',
        ];
    }

    /**
     * Get the project that owns the channel.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the alert rules attached to this channel.
     *
     * @return BelongsToMany<AlertRule, $this, AlertRuleChannel, 'pivot'>
     */
    public function alertRules(): BelongsToMany
    {
        return $this->belongsToMany(AlertRule::class, 'alert_rule_channel')
            ->using(AlertRuleChannel::class)
            ->withPivot('recipient_filter')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include enabled channels.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
