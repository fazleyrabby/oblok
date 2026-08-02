<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeploymentResource;
use App\Models\Deployment;
use App\Models\Project;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeploymentController extends Controller
{
    /**
     * Display a paginated list of deployments for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Deployment::class, $project]);

        $deployments = $project->deployments()->paginate(15);

        return DeploymentResource::collection($deployments);
    }

    /**
     * Display the specified deployment.
     */
    public function show(Project $project, Deployment $deployment): DeploymentResource
    {
        $this->authorize('view', $deployment);

        return new DeploymentResource($deployment);
    }
}
