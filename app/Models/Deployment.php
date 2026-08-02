<?php

namespace App\Models;

use Database\Factories\DeploymentFactory;
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
 * @property string $environment
 * @property string|null $commit_hash
 * @property string|null $commit_message
 * @property string|null $author
 * @property string $status
 * @property array<string, mixed>|null $payload
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'environment', 'commit_hash', 'commit_message', 'author', 'status', 'payload', 'started_at', 'finished_at'])]
class Deployment extends Model
{
    /** @use HasFactory<DeploymentFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the deployment.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope a query to only include successful deployments.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'successful');
    }

    /**
     * Scope a query to only include failed deployments.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Determine if the deployment was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }
}
