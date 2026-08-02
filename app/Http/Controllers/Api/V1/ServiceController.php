<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Services\PingServiceHealth;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\HealthCheckResultResource;
use App\Http\Resources\ServiceResource;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ServiceController extends Controller
{
    /**
     * Display a listing of services for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $services = $project->services()->with('healthCheckResults')->latest()->get();

        return ServiceResource::collection($services);
    }

    /**
     * Store a newly created service.
     */
    public function store(StoreServiceRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $service = $project->services()->create($request->validated());

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified service.
     */
    public function show(Project $project, Service $service): ServiceResource
    {
        $this->authorize('view', $service);

        $service->load('healthCheckResults');

        return new ServiceResource($service);
    }

    /**
     * Update the specified service.
     */
    public function update(UpdateServiceRequest $request, Project $project, Service $service): ServiceResource
    {
        $this->authorize('update', $service);

        $service->update($request->validated());

        return new ServiceResource($service);
    }

    /**
     * Trigger an instant ping probe.
     */
    public function ping(Project $project, Service $service, PingServiceHealth $pingServiceHealth): JsonResponse
    {
        $this->authorize('update', $service);

        $result = $pingServiceHealth->handle($service);

        return response()->json([
            'data' => new HealthCheckResultResource($result),
            'meta' => [
                'message' => 'Health check probe completed successfully.',
            ],
        ]);
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Project $project, Service $service): Response
    {
        $this->authorize('delete', $service);

        $service->delete();

        return response()->noContent();
    }
}
