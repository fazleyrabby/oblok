<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Deployments\ProcessDeploymentWebhook;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeploymentResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeploymentWebhookController extends Controller
{
    /**
     * Receive and process an incoming deployment webhook payload for a project.
     */
    public function __invoke(Request $request, Project $project, ProcessDeploymentWebhook $processWebhook): JsonResponse
    {
        $deployment = $processWebhook->handle($project, $request->all());

        return (new DeploymentResource($deployment))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
