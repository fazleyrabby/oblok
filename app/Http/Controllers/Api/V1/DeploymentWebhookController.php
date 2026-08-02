<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Deployments\ProcessDeploymentWebhook;
use App\Actions\Webhooks\CaptureWebhook;
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
    public function __invoke(
        Request $request,
        Project $project,
        CaptureWebhook $captureWebhook,
        ProcessDeploymentWebhook $processWebhook,
    ): JsonResponse {
        $startedAt = hrtime(true);

        $webhookCall = $captureWebhook->handle($project, [
            'event' => 'deployment',
            'method' => $request->method(),
            'url' => $request->path(),
            'request_headers' => $request->headers->all(),
            'request_payload' => $request->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $deployment = $processWebhook->handle($project, $request->all());

        $webhookCall->update([
            'status_code' => Response::HTTP_CREATED,
            'response_payload' => ['deployment_id' => $deployment->id],
            'processing_time_ms' => (int) round((hrtime(true) - $startedAt) / 1_000_000),
        ]);

        return (new DeploymentResource($deployment))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
