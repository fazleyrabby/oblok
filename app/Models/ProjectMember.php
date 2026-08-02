<?php

namespace App\Models;

use App\Enums\ProjectRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property string $project_id
 * @property int $user_id
 * @property ProjectRole $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ProjectMember extends Pivot
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ProjectRole::class,
        ];
    }

    /**
     * Get the project this membership belongs to.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user this membership belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine whether this member is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === ProjectRole::Admin;
    }

    /**
     * Determine whether this member is an operator.
     */
    public function isOperator(): bool
    {
        return $this->role === ProjectRole::Operator;
    }

    /**
     * Determine whether this member is a viewer.
     */
    public function isViewer(): bool
    {
        return $this->role === ProjectRole::Viewer;
    }

    /**
     * Determine whether this member's role grants the given ability.
     */
    public function can(string $ability): bool
    {
        return $this->role->can($ability);
    }
}
