<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Authorize authenticated users to listen on private channels. Channels are
| scoped to a project so that only project members receive realtime events.
|
*/

Broadcast::channel('projects.{projectId}', function (User $user, string $projectId) {
    $project = Project::find($projectId);

    return $project && $user->can('view', $project);
});
