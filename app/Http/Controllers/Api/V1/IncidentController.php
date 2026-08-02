<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Incidents\CreateIncident;
use App\Actions\Incidents\ResolveIncident;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IncidentController extends Controller
{
    /**
     * Display a paginated list of incidents for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Incident::class, $project]);

        $incidents = $project->incidents()->paginate(15);

        return IncidentResource::collection($incidents);
    }

    /**
     * Create a new incident.
     */
    public function store(StoreIncidentRequest $request, Project $project, CreateIncident $createIncident): JsonResponse
    {
        $incident = $createIncident->handle($project, $request->validated());

        return (new IncidentResource($incident))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified incident.
     */
    public function show(Project $project, Incident $incident): IncidentResource
    {
        $this->authorize('view', $incident);

        return new IncidentResource($incident);
    }

    /**
     * Resolve an active incident.
     */
    public function resolve(Project $project, Incident $incident, ResolveIncident $resolveIncident): IncidentResource
    {
        $this->authorize('update', $incident);

        $resolved = $resolveIncident->handle($incident);

        return new IncidentResource($resolved);
    }
}
