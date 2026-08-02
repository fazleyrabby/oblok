<?php

namespace App\Policies;

use App\Models\MessagingIntegration;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class MessagingIntegrationPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view the project's messaging integration.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the integration.
     */
    public function view(User $user, MessagingIntegration $integration): bool
    {
        return $this->memberRole($user, $integration->project) !== null;
    }

    /**
     * Determine whether the user can connect a chat platform.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageIntegrations') ?? false;
    }

    /**
     * Determine whether the user can send messages through the integration.
     */
    public function send(User $user, MessagingIntegration $integration): bool
    {
        return $this->memberRole($user, $integration->project)?->can('manageIntegrations') ?? false;
    }

    /**
     * Determine whether the user can disconnect the integration.
     */
    public function delete(User $user, MessagingIntegration $integration): bool
    {
        return $this->memberRole($user, $integration->project)?->can('manageIntegrations') ?? false;
    }
}
