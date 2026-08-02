<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordTaskRunRequest;
use App\Http\Requests\StoreScheduledTaskRequest;
use App\Http\Requests\UpdateScheduledTaskRequest;
use App\Http\Resources\ScheduledTaskResource;
use App\Http\Resources\TaskRunResource;
use App\Models\Project;
use App\Models\ScheduledTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ScheduledTaskController extends Controller
{
    /**
     * Display a paginated list of scheduled tasks for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [ScheduledTask::class, $project]);

        return ScheduledTaskResource::collection(
            $project->scheduledTasks()->with('runs')->paginate(15)
        );
    }

    /**
     * Store a newly created scheduled task.
     */
    public function store(StoreScheduledTaskRequest $request, Project $project): JsonResponse
    {
        $data = $request->safe()->except('enabled');
        $data['enabled'] = $request->boolean('enabled', true);

        $scheduledTask = $project->scheduledTasks()->create($data);

        $scheduledTask->update(['next_run_at' => $scheduledTask->calculateNextRun()]);

        return (new ScheduledTaskResource($scheduledTask))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display a single scheduled task with its run history.
     */
    public function show(Project $project, ScheduledTask $scheduledTask): ScheduledTaskResource
    {
        $this->authorize('view', $scheduledTask);

        $scheduledTask->load('runs');

        return new ScheduledTaskResource($scheduledTask);
    }

    /**
     * Update the scheduled task.
     */
    public function update(UpdateScheduledTaskRequest $request, Project $project, ScheduledTask $scheduledTask): ScheduledTaskResource
    {
        $scheduledTask->update($request->safe()->all());

        if ($request->has('cron_expression')) {
            $scheduledTask->update(['next_run_at' => $scheduledTask->calculateNextRun()]);
        }

        return new ScheduledTaskResource($scheduledTask->fresh());
    }

    /**
     * Delete the scheduled task.
     */
    public function destroy(Project $project, ScheduledTask $scheduledTask): JsonResponse
    {
        $this->authorize('delete', $scheduledTask);

        $scheduledTask->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Record a run for a scheduled task.
     */
    public function recordRun(RecordTaskRunRequest $request, Project $project, ScheduledTask $scheduledTask): TaskRunResource
    {
        $run = $scheduledTask->recordRun($request->validated());

        return new TaskRunResource($run);
    }
}
