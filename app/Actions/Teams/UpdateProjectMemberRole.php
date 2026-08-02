<?php

namespace App\Actions\Teams;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateProjectMemberRole
{
    /**
     * Update the role of an existing project member.
     */
    public function handle(Project $project, User $user, ProjectRole $role, ?User $actor = null): void
    {
        if ($user->id === $project->user_id) {
            throw ValidationException::withMessages([
                'role' => 'The project owner role cannot be changed.',
            ]);
        }

        if ($actor && $user->id === $actor->id) {
            throw ValidationException::withMessages([
                'role' => 'You cannot change your own role.',
            ]);
        }

        $existingRole = $project->members()->where('users.id', $user->id)->value('project_members.role');

        if (! $existingRole) {
            throw ValidationException::withMessages([
                'user' => 'This user is not a member of the project.',
            ]);
        }

        if ($actor) {
            $actorRole = $project->members()->where('users.id', $actor->id)->value('project_members.role');

            $actorLevel = $actorRole === null ? 4 : $this->roleLevel(ProjectRole::from($actorRole));
            $targetLevel = $this->roleLevel($role);
            $currentLevel = $this->roleLevel(ProjectRole::from($existingRole));

            if ($targetLevel > $actorLevel) {
                throw ValidationException::withMessages([
                    'role' => 'You cannot assign a role more privileged than your own.',
                ]);
            }

            if ($currentLevel > $actorLevel) {
                throw ValidationException::withMessages([
                    'role' => 'You cannot change the role of a member more privileged than yourself.',
                ]);
            }
        }

        $project->members()->updateExistingPivot($user->id, ['role' => $role->value]);
    }

    /**
     * Role privilege level, higher is more privileged.
     */
    private function roleLevel(ProjectRole $role): int
    {
        return match ($role) {
            ProjectRole::Owner => 4,
            ProjectRole::Admin => 3,
            ProjectRole::Operator => 2,
            ProjectRole::Viewer => 1,
        };
    }
}
