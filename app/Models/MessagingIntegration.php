<?php

namespace App\Models;

use App\Enums\MessagingPlatform;
use Database\Factories\MessagingIntegrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property MessagingPlatform $platform
 * @property string $name
 * @property array<string, mixed> $config
 * @property string|null $channel
 * @property bool $enabled
 * @property Carbon|null $last_connected_at
 * @property-read Project $project
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'project_id', 'platform', 'name', 'config', 'channel', 'enabled', 'last_connected_at',
])]
class MessagingIntegration extends Model
{
    /** @use HasFactory<MessagingIntegrationFactory> */
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'messaging_integrations';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => MessagingPlatform::class,
            'config' => 'encrypted:array',
            'enabled' => 'boolean',
            'last_connected_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the integration.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope a query to only include enabled integrations.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope a query to only include integrations for a given platform.
     *
     * @param  Builder<$this>  $query
     */
    public function scopePlatform(Builder $query, MessagingPlatform $platform): Builder
    {
        return $query->where('platform', $platform->value);
    }
}
