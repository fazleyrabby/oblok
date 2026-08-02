<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationChannelRequest;
use App\Http\Requests\UpdateNotificationChannelRequest;
use App\Http\Resources\NotificationChannelResource;
use App\Models\NotificationChannel;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NotificationChannelController extends Controller
{
    /**
     * Display a paginated list of notification channels for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [NotificationChannel::class, $project]);

        $channels = $project->notificationChannels()->paginate(15);

        return NotificationChannelResource::collection($channels);
    }

    /**
     * Create a new notification channel.
     */
    public function store(StoreNotificationChannelRequest $request, Project $project): JsonResponse
    {
        $channel = $project->notificationChannels()->create($this->normalizeConfig($request->validated()));

        return (new NotificationChannelResource($channel))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified notification channel.
     */
    public function show(Project $project, NotificationChannel $notificationChannel): NotificationChannelResource
    {
        $this->authorize('view', $notificationChannel);

        return new NotificationChannelResource($notificationChannel);
    }

    /**
     * Update the notification channel.
     */
    public function update(UpdateNotificationChannelRequest $request, Project $project, NotificationChannel $notificationChannel): NotificationChannelResource
    {
        $notificationChannel->update($this->normalizeConfig($request->validated()));

        return new NotificationChannelResource($notificationChannel->fresh());
    }

    /**
     * Delete the notification channel.
     */
    public function destroy(Project $project, NotificationChannel $notificationChannel): Response
    {
        $this->authorize('delete', $notificationChannel);

        $notificationChannel->delete();

        return response()->noContent();
    }

    /**
     * Map the flat "config" field to the encrypted_config column.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeConfig(array $data): array
    {
        if (isset($data['config'])) {
            $data['encrypted_config'] = $data['config'];
            unset($data['config']);
        }

        return $data;
    }
}
