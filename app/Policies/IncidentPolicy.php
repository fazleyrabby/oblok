<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class IncidentPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any incidents for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the incident.
     */
    public function view(User $user, Incident $incident): bool
    {
        return $this->memberRole($user, $incident->project) !== null;
    }

    /**
     * Determine whether the user can create incidents.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageIncidents') ?? false;
    }

    /**
     * Determine whether the user can update/resolve the incident.
     */
    public function update(User $user, Incident $incident): bool
    {
        return $this->memberRole($user, $incident->project)?->can('manageIncidents') ?? false;
    }

    /**
     * Determine whether the user can delete the incident.
     */
    public function delete(User $user, Incident $incident): bool
    {
        return $this->memberRole($user, $incident->project)?->can('manageIncidents') ?? false;
    }
}
