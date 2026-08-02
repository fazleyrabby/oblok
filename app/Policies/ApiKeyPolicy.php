<?php

namespace App\Policies;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class ApiKeyPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view the project's API keys.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the key.
     */
    public function view(User $user, ApiKey $key): bool
    {
        return $this->memberRole($user, $key->project) !== null;
    }

    /**
     * Determine whether the user can create an API key for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageApiKeys') ?? false;
    }

    /**
     * Determine whether the user can revoke the key.
     */
    public function delete(User $user, ApiKey $key): bool
    {
        return $this->memberRole($user, $key->project)?->can('manageApiKeys') ?? false;
    }
}
