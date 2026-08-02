<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ScheduledTask;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class ScheduledTaskPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any scheduled tasks for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the scheduled task.
     */
    public function view(User $user, ScheduledTask $scheduledTask): bool
    {
        return $this->memberRole($user, $scheduledTask->project) !== null;
    }

    /**
     * Determine whether the user can create scheduled tasks for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageScheduler') ?? false;
    }

    /**
     * Determine whether the user can update the scheduled task.
     */
    public function update(User $user, ScheduledTask $scheduledTask): bool
    {
        return $this->memberRole($user, $scheduledTask->project)?->can('manageScheduler') ?? false;
    }

    /**
     * Determine whether the user can delete the scheduled task.
     */
    public function delete(User $user, ScheduledTask $scheduledTask): bool
    {
        return $this->memberRole($user, $scheduledTask->project)?->can('manageScheduler') ?? false;
    }

    /**
     * Determine whether the user can record a run for the scheduled task.
     */
    public function recordRun(User $user, ScheduledTask $scheduledTask): bool
    {
        return $this->memberRole($user, $scheduledTask->project)?->can('manageScheduler') ?? false;
    }
}
