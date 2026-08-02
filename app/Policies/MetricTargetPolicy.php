<?php

namespace App\Policies;

use App\Models\MetricTarget;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class MetricTargetPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view the project's scrape targets.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can register a scrape target.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('manageMetrics') ?? false;
    }

    /**
     * Determine whether the user can remove a scrape target.
     */
    public function delete(User $user, MetricTarget $target): bool
    {
        return $this->memberRole($user, $target->project)?->can('manageMetrics') ?? false;
    }
}
