<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertEventResource;
use App\Models\AlertEvent;
use App\Models\Project;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertEventController extends Controller
{
    /**
     * Display a paginated list of alert events for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [AlertEvent::class, $project]);

        $events = $project->alertEvents()
            ->with('alertRule', 'deliveries')
            ->latest('triggered_at')
            ->paginate(15);

        return AlertEventResource::collection($events);
    }

    /**
     * Display the specified alert event.
     */
    public function show(Project $project, AlertEvent $alertEvent): AlertEventResource
    {
        $this->authorize('view', $alertEvent);

        return new AlertEventResource($alertEvent->load('alertRule', 'deliveries'));
    }
}
