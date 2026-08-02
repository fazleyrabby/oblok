<?php

namespace App\Models;

use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string|null $service_id
 * @property string $title
 * @property string|null $description
 * @property string $severity
 * @property string $status
 * @property Carbon $started_at
 * @property Carbon|null $resolved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'service_id', 'title', 'description', 'severity', 'status', 'started_at', 'resolved_at'])]
class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the incident.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the service associated with the incident.
     *
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope a query to only include open incidents.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', '!=', 'resolved');
    }

    /**
     * Scope a query to only include resolved incidents.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Determine if the incident is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Resolve the incident.
     */
    public function resolve(): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }
}
