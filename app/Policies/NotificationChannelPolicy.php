<?php

namespace App\Policies;

use App\Models\NotificationChannel;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class NotificationChannelPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any channels for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the channel.
     */
    public function view(User $user, NotificationChannel $channel): bool
    {
        return $this->memberRole($user, $channel->project) !== null;
    }

    /**
     * Determine whether the user can create channels for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageAlerts') ?? false;
    }

    /**
     * Determine whether the user can update the channel.
     */
    public function update(User $user, NotificationChannel $channel): bool
    {
        return $this->memberRole($user, $channel->project)?->can('manageAlerts') ?? false;
    }

    /**
     * Determine whether the user can delete the channel.
     */
    public function delete(User $user, NotificationChannel $channel): bool
    {
        return $this->memberRole($user, $channel->project)?->can('manageAlerts') ?? false;
    }
}
