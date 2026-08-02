<?php

namespace App\Policies\Concerns;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;

trait ResolvesProjectMembership
{
    /**
     * Resolve the effective role a user holds on a project.
     *
     * The project owner is always treated as Owner even when no pivot row exists.
     */
    protected function memberRole(?User $user, Project $project): ?ProjectRole
    {
        if (! $user) {
            return null;
        }

        if ($project->user_id === $user->id) {
            return ProjectRole::Owner;
        }

        $role = $project->members()->where('users.id', $user->id)->value('project_members.role');

        return $role ? ProjectRole::tryFrom($role) : null;
    }
}
