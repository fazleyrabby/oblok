<?php

namespace App\Actions\Teams;

use App\Models\Project;
use App\Models\User;

class RemoveProjectMember
{
    /**
     * Remove a member from a project team.
     */
    public function handle(Project $project, User $user): void
    {
        $project->members()->detach($user->id);
    }
}
