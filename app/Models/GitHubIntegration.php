<?php

namespace App\Models;

use Database\Factories\GitHubIntegrationFactory;
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
 * @property string $project_id
 * @property string $repository_owner
 * @property string $repository_name
 * @property string|null $access_token
 * @property string|null $default_branch
 * @property bool $enabled
 * @property Carbon|null $last_synced_at
 * @property-read Project $project
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'project_id', 'repository_owner', 'repository_name', 'access_token',
    'default_branch', 'enabled', 'last_synced_at',
])]
class GitHubIntegration extends Model
{
    /** @use HasFactory<GitHubIntegrationFactory> */
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'github_integrations';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'enabled' => 'boolean',
            'last_synced_at' => 'datetime',
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
     * Get the commits captured for this integration.
     *
     * @return HasMany<GitHubCommit, $this>
     */
    public function commits(): HasMany
    {
        return $this->hasMany(GitHubCommit::class, 'github_integration_id')->latest('authored_at');
    }

    /**
     * Get the pull requests captured for this integration.
     *
     * @return HasMany<GitHubPullRequest, $this>
     */
    public function pullRequests(): HasMany
    {
        return $this->hasMany(GitHubPullRequest::class, 'github_integration_id')->latest('opened_at');
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
     * The "owner/name" slug identifying the linked repository.
     */
    public function repositorySlug(): string
    {
        return "{$this->repository_owner}/{$this->repository_name}";
    }
}
