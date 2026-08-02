<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Integrations\ConnectMessagingIntegration;
use App\Actions\Integrations\DisconnectMessagingIntegration;
use App\Actions\Integrations\SendMessagingMessage;
use App\Enums\MessagingPlatform;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessagingMessageRequest;
use App\Http\Requests\StoreMessagingIntegrationRequest;
use App\Http\Resources\ChatChannelResource;
use App\Http\Resources\MessagingIntegrationResource;
use App\Models\MessagingIntegration;
use App\Models\Project;
use App\Services\Messaging\Exceptions\MessagingApiException;
use App\Services\Messaging\MessagingDriverRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessagingIntegrationController extends Controller
{
    /**
     * Display the project's messaging integration.
     */
    public function index(Project $project): MessagingIntegrationResource|JsonResponse
    {
        $this->authorize('viewAny', [MessagingIntegration::class, $project]);

        $integration = $project->messagingIntegration;

        if (! $integration) {
            return response()->json(['data' => null]);
        }

        return new MessagingIntegrationResource($integration);
    }

    /**
     * Connect a chat platform to the project.
     */
    public function store(
        StoreMessagingIntegrationRequest $request,
        Project $project,
        ConnectMessagingIntegration $connect
    ): MessagingIntegrationResource|JsonResponse {
        $platform = MessagingPlatform::from($request->validated('platform'));

        try {
            $integration = $connect->handle(
                $project,
                $platform,
                ['bot_token' => $request->validated('bot_token')],
                $request->validated('channel')
            );
        } catch (MessagingApiException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new MessagingIntegrationResource($integration);
    }

    /**
     * List the channels the integration can post messages to.
     */
    public function channels(Project $project, MessagingIntegration $integration, MessagingDriverRegistry $drivers): AnonymousResourceCollection
    {
        $this->authorize('view', $integration);

        return ChatChannelResource::collection($drivers->for($integration->platform)->channels($integration->config));
    }

    /**
     * Send a message through the connected integration.
     */
    public function send(
        SendMessagingMessageRequest $request,
        Project $project,
        MessagingIntegration $integration,
        SendMessagingMessage $send
    ): JsonResponse {
        try {
            $send->handle($integration, $request->validated('channel'), $request->validated('message'));
        } catch (MessagingApiException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Message sent successfully.']);
    }

    /**
     * Disconnect the messaging integration from the project.
     */
    public function destroy(Project $project, MessagingIntegration $integration, DisconnectMessagingIntegration $disconnect): JsonResponse
    {
        $this->authorize('delete', $integration);

        $disconnect->handle($integration);

        return response()->json(['message' => 'Messaging integration disconnected.']);
    }
}
