<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
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
 * @property string $type
 * @property string $target
 * @property int $check_interval
 * @property int $timeout
 * @property int $expected_status_code
 * @property string $status
 * @property Carbon|null $last_checked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'name', 'type', 'target', 'check_interval', 'timeout', 'expected_status_code', 'status', 'last_checked_at'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_interval' => 'integer',
            'timeout' => 'integer',
            'expected_status_code' => 'integer',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the service.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the health check results for the service.
     *
     * @return HasMany<HealthCheckResult, $this>
     */
    public function healthCheckResults(): HasMany
    {
        return $this->hasMany(HealthCheckResult::class)->latest('created_at');
    }

    /**
     * Scope a query to only include healthy services.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeHealthy(Builder $query): Builder
    {
        return $query->where('status', 'healthy');
    }

    /**
     * Scope a query to only include failing services.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFailing(Builder $query): Builder
    {
        return $query->where('status', 'failing');
    }

    /**
     * Determine if the service is currently healthy.
     */
    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }
}
