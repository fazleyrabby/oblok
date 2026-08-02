<?php

namespace App\Policies;

use App\Models\LogEntry;
use App\Models\Project;
use App\Models\User;

class LogEntryPolicy
{
    /**
     * Determine whether the user can view logs for the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $project->user_id === $user->id;
    }

    /**
     * Determine whether the user can view the specific log entry.
     */
    public function view(User $user, LogEntry $logEntry): bool
    {
        return $logEntry->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can ingest log entries for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $project->user_id === $user->id;
    }
}
