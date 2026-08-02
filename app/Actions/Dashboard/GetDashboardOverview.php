<?php

namespace App\Actions\Dashboard;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetDashboardOverview
{
    /**
     * Get aggregated metrics and recent activity for the user's dashboard.
     *
     * @return array{
     *     total_projects: int,
     *     active_projects: int,
     *     archived_projects: int,
     *     recent_projects: Collection<int, Project>,
     *     uptime_percentage: float,
     *     active_incidents: int
     * }
     */
    public function handle(User $user): array
    {
        $projectsQuery = Project::forUser($user);

        $totalProjects = (clone $projectsQuery)->count();
        $activeProjects = (clone $projectsQuery)->active()->count();
        $archivedProjects = (clone $projectsQuery)->archived()->count();

        $recentProjects = (clone $projectsQuery)
            ->active()
            ->latest()
            ->take(5)
            ->get();

        return [
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'archived_projects' => $archivedProjects,
            'recent_projects' => $recentProjects,
            'uptime_percentage' => 100.0,
            'active_incidents' => 0,
        ];
    }
}
