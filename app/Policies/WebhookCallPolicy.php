<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Models\WebhookCall;
use App\Policies\Concerns\ResolvesProjectMembership;

class WebhookCallPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view any webhook calls for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the webhook call.
     */
    public function view(User $user, WebhookCall $webhookCall): bool
    {
        return $this->memberRole($user, $webhookCall->project) !== null;
    }

    /**
     * Determine whether the user can replay the webhook call.
     */
    public function replay(User $user, WebhookCall $webhookCall): bool
    {
        return $this->memberRole($user, $webhookCall->project)?->can('manageWebhooks') ?? false;
    }
}
