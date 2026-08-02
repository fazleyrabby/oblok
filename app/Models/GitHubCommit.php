<?php

namespace App\Models;

use Database\Factories\GitHubCommitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $github_integration_id
 * @property string $sha
 * @property string $message
 * @property string $author_name
 * @property string|null $author_email
 * @property Carbon|null $authored_at
 * @property string|null $url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'github_integration_id', 'sha', 'message', 'author_name',
    'author_email', 'authored_at', 'url',
])]
class GitHubCommit extends Model
{
    /** @use HasFactory<GitHubCommitFactory> */
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'github_commits';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'authored_at' => 'datetime',
        ];
    }

    /**
     * Get the integration this commit belongs to.
     *
     * @return BelongsTo<GitHubIntegration, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(GitHubIntegration::class, 'github_integration_id');
    }
}
