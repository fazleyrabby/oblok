<?php

namespace App\Policies;

use App\Models\GitHubIntegration;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class GitHubIntegrationPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view the project's GitHub integration.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the integration.
     */
    public function view(User $user, GitHubIntegration $integration): bool
    {
        return $this->memberRole($user, $integration->project) !== null;
    }

    /**
     * Determine whether the user can connect a GitHub integration.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageIntegrations') ?? false;
    }

    /**
     * Determine whether the user can trigger a data sync.
     */
    public function sync(User $user, GitHubIntegration $integration): bool
    {
        return $this->memberRole($user, $integration->project)?->can('manageIntegrations') ?? false;
    }

    /**
     * Determine whether the user can disconnect the integration.
     */
    public function delete(User $user, GitHubIntegration $integration): bool
    {
        return $this->memberRole($user, $integration->project)?->can('manageIntegrations') ?? false;
    }
}
