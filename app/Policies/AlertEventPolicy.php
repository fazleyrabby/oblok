<?php

namespace App\Policies;

use App\Models\AlertEvent;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class AlertEventPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any alert events for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the alert event.
     */
    public function view(User $user, AlertEvent $alertEvent): bool
    {
        return $this->memberRole($user, $alertEvent->project) !== null;
    }
}
