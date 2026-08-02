<?php

namespace App\Policies;

use App\Models\Deployment;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class DeploymentPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any deployments for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the deployment.
     */
    public function view(User $user, Deployment $deployment): bool
    {
        return $this->memberRole($user, $deployment->project) !== null;
    }

    /**
     * Determine whether the user can create deployments.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageDeployments') ?? false;
    }
}
