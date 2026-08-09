<?php

namespace App\Models;

use App\Enums\RunbookType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $name
 * @property string|null $description
 * @property RunbookType $type
 * @property array<string, mixed>|null $config
 * @property string $trigger_type
 * @property bool $enabled
 * @property int $cooldown_minutes
 * @property int $timeout_seconds
 * @property Carbon|null $last_executed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'project_id', 'name', 'description', 'type', 'config',
    'trigger_type', 'enabled', 'cooldown_minutes', 'timeout_seconds',
    'last_executed_at',
])]
class Runbook extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RunbookType::class,
            'config' => 'array',
            'enabled' => 'boolean',
            'cooldown_minutes' => 'integer',
            'timeout_seconds' => 'integer',
            'last_executed_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the runbook.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get execution runs for this runbook.
     *
     * @return HasMany<RunbookRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(RunbookRun::class)->latest();
    }

    /**
     * Scope query to enabled runbooks.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Determine whether the runbook is in its cooldown window.
     */
    public function isInCooldown(): bool
    {
        if (! $this->last_executed_at) {
            return false;
        }

        return $this->last_executed_at->copy()->addMinutes($this->cooldown_minutes)->isFuture();
    }
}
