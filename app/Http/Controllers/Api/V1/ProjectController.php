<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Projects\ArchiveProject;
use App\Actions\Projects\CreateProject;
use App\Actions\Projects\UpdateProject;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Project::forUser($user);

        if ($request->boolean('archived')) {
            $query->archived();
        } else {
            $query->active();
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $projects = $query->latest()->paginate(
            min((int) $request->input('per_page', 25), 100)
        );

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request, CreateProject $createProject): JsonResponse
    {
        $project = $createProject->handle($request->user(), $request->validated());

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project);
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project, UpdateProject $updateProject): ProjectResource
    {
        $project = $updateProject->handle($project, $request->validated());

        return new ProjectResource($project);
    }

    /**
     * Archive or unarchive the specified project.
     */
    public function archive(Request $request, Project $project, ArchiveProject $archiveProject): ProjectResource
    {
        $this->authorize('update', $project);

        $archive = $request->boolean('archive', true);
        $project = $archiveProject->handle($project, $archive);

        return new ProjectResource($project);
    }

    /**
     * Soft delete the specified project.
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(null, 204);
    }
}
