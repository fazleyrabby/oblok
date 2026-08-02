<?php

namespace App\Policies;

use App\Models\LogEntry;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ResolvesProjectMembership;

class LogEntryPolicy
{
    use ResolvesProjectMembership;

    /**
     * Determine whether the user can view logs for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project) !== null;
    }

    /**
     * Determine whether the user can view the specific log entry.
     */
    public function view(User $user, LogEntry $logEntry): bool
    {
        return $this->memberRole($user, $logEntry->project) !== null;
    }

    /**
     * Determine whether the user can ingest log entries for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->memberRole($user, $project)?->can('ingestLogs') ?? false;
    }
}
