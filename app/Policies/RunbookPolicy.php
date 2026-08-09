<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Runbook;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class RunbookPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any runbooks for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the runbook.
     */
    public function view(User $user, Runbook $runbook): bool
    {
        return $this->memberRole($user, $runbook->project) !== null;
    }

    /**
     * Determine whether the user can create runbooks for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can update the runbook.
     */
    public function update(User $user, Runbook $runbook): bool
    {
        return $this->memberRole($user, $runbook->project) !== null;
    }

    /**
     * Determine whether the user can delete the runbook.
     */
    public function delete(User $user, Runbook $runbook): bool
    {
        return $this->memberRole($user, $runbook->project) !== null;
    }

    /**
     * Determine whether the user can execute the runbook manually.
     */
    public function execute(User $user, Runbook $runbook): bool
    {
        return $this->memberRole($user, $runbook->project) !== null;
    }
}
