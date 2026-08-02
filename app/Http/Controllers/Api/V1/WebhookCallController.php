<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Webhooks\ReplayWebhook;
use App\Http\Controllers\Controller;
use App\Http\Resources\WebhookCallResource;
use App\Models\Project;
use App\Models\WebhookCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use InvalidArgumentException;

class WebhookCallController extends Controller
{
    /**
     * Display a paginated list of webhook calls for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [WebhookCall::class, $project]);

        return WebhookCallResource::collection(
            $project->webhookCalls()->paginate(15)
        );
    }

    /**
     * Display a single webhook call with its captured payload.
     */
    public function show(Project $project, WebhookCall $webhookCall): WebhookCallResource
    {
        $this->authorize('view', $webhookCall);

        return new WebhookCallResource($webhookCall);
    }

    /**
     * Replay a captured webhook call through its registered processor.
     */
    public function replay(Project $project, WebhookCall $webhookCall, ReplayWebhook $replayWebhook): WebhookCallResource|JsonResponse
    {
        $this->authorize('replay', $webhookCall);

        try {
            $replayed = $replayWebhook->handle($webhookCall);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new WebhookCallResource($replayed);
    }
}
