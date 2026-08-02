<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SnoozeNotificationDeliveryRequest;
use App\Http\Resources\NotificationDeliveryResource;
use App\Models\NotificationDelivery;
use App\Models\Project;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationDeliveryController extends Controller
{
    /**
     * Display a paginated list of notification deliveries for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $deliveries = $project->notificationDeliveries()
            ->with('channel', 'alertRule')
            ->latest('created_at')
            ->paginate(15);

        return NotificationDeliveryResource::collection($deliveries);
    }

    /**
     * Acknowledge a notification delivery.
     */
    public function acknowledge(Project $project, NotificationDelivery $delivery): NotificationDeliveryResource
    {
        $this->authorize('update', $project);

        $delivery->acknowledge(auth()->user());

        return new NotificationDeliveryResource($delivery->fresh());
    }

    /**
     * Snooze a notification delivery until a given time.
     */
    public function snooze(SnoozeNotificationDeliveryRequest $request, Project $project, NotificationDelivery $delivery): NotificationDeliveryResource
    {
        $this->authorize('update', $project);

        $until = $request->filled('until') ? $request->date('until') : now()->addHours(2);

        $delivery->snooze($until);

        return new NotificationDeliveryResource($delivery->fresh());
    }
}
