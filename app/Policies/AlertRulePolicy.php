<?php

namespace App\Policies;

use App\Models\AlertRule;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class AlertRulePolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any alert rules for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the alert rule.
     */
    public function view(User $user, AlertRule $alertRule): bool
    {
        return $this->memberRole($user, $alertRule->project) !== null;
    }

    /**
     * Determine whether the user can create alert rules for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageAlerts') ?? false;
    }

    /**
     * Determine whether the user can update the alert rule.
     */
    public function update(User $user, AlertRule $alertRule): bool
    {
        return $this->memberRole($user, $alertRule->project)?->can('manageAlerts') ?? false;
    }

    /**
     * Determine whether the user can delete the alert rule.
     */
    public function delete(User $user, AlertRule $alertRule): bool
    {
        return $this->memberRole($user, $alertRule->project)?->can('manageAlerts') ?? false;
    }
}
