<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Deployment;
use App\Models\Project;
use Illuminate\View\View;

class DeploymentController extends Controller
{
    /**
     * Display deployment history timeline for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('view', $project);

        $deployments = $project->deployments()->paginate(15);

        return view('deployments.index', compact('project', 'deployments'));
    }

    /**
     * Display deployment details.
     */
    public function show(Project $project, Deployment $deployment): View
    {
        $this->authorize('view', $deployment);

        return view('deployments.show', compact('project', 'deployment'));
    }
}
