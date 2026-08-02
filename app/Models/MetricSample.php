<?php

namespace App\Models;

use Database\Factories\MetricSampleFactory;
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
 * @property string $name
 * @property array<string, string|int|float> $labels
 * @property float $value
 * @property Carbon $recorded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'name', 'labels', 'value', 'recorded_at'])]
class MetricSample extends Model
{
    /** @use HasFactory<MetricSampleFactory> */
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'metric_samples';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'value' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the sample.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope a query to samples recorded for the given project.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeForProject(Builder $query, string $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope a query to samples with the given metric name.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeNamed(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    /**
     * Scope a query to samples recorded at or after the given time.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeSince(Builder $query, Carbon $from): Builder
    {
        return $query->where('recorded_at', '>=', $from);
    }
}
