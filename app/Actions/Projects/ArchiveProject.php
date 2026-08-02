<?php

namespace App\Actions\Projects;

use App\Models\Project;

class ArchiveProject
{
    /**
     * Archive or unarchive a project.
     */
    public function handle(Project $project, bool $archive = true): Project
    {
        if ($archive) {
            $project->archive();
        } else {
            $project->unarchive();
        }

        return $project->refresh();
    }
}
