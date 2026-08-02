<?php

namespace App\Policies;

use App\Models\Deployment;
use App\Models\Project;
use App\Models\User;

class DeploymentPolicy
{
    /**
     * Determine whether the user can view any deployments for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $project->user_id === $user->id;
    }

    /**
     * Determine whether the user can view the deployment.
     */
    public function view(User $user, Deployment $deployment): bool
    {
        return $deployment->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can create deployments.
     */
    public function create(User $user, Project $project): bool
    {
        return $project->user_id === $user->id;
    }
}
