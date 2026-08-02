<?php

namespace App\Models;

use Database\Factories\GitHubPullRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $github_integration_id
 * @property int $number
 * @property string $title
 * @property string|null $body
 * @property string $state
 * @property string $author_name
 * @property Carbon|null $opened_at
 * @property Carbon|null $merged_at
 * @property Carbon|null $closed_at
 * @property string|null $url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'github_integration_id', 'number', 'title', 'body', 'state',
    'author_name', 'opened_at', 'merged_at', 'closed_at', 'url',
])]
class GitHubPullRequest extends Model
{
    /** @use HasFactory<GitHubPullRequestFactory> */
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'github_pull_requests';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'merged_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Get the integration this pull request belongs to.
     *
     * @return BelongsTo<GitHubIntegration, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(GitHubIntegration::class, 'github_integration_id');
    }

    /**
     * Scope a query to pull requests in the given state (open/closed/all).
     *
     * @param  Builder<$this>  $query
     */
    public function scopeState(Builder $query, string $state): Builder
    {
        return $state === 'all' ? $query : $query->where('state', $state);
    }
}
