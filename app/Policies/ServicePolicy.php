<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class ServicePolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any services under the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the service.
     */
    public function view(User $user, Service $service): bool
    {
        return $this->memberRole($user, $service->project) !== null;
    }

    /**
     * Determine whether the user can create services under the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageServices') ?? false;
    }

    /**
     * Determine whether the user can update the service.
     */
    public function update(User $user, Service $service): bool
    {
        return $this->memberRole($user, $service->project)?->can('manageServices') ?? false;
    }

    /**
     * Determine whether the user can delete the service.
     */
    public function delete(User $user, Service $service): bool
    {
        return $this->memberRole($user, $service->project)?->can('manageServices') ?? false;
    }
}
