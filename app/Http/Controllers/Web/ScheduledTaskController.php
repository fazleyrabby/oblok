<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordTaskRunRequest;
use App\Http\Requests\StoreScheduledTaskRequest;
use App\Http\Requests\UpdateScheduledTaskRequest;
use App\Models\Project;
use App\Models\ScheduledTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduledTaskController extends Controller
{
    /**
     * Display a listing of scheduled tasks for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [ScheduledTask::class, $project]);

        $scheduledTasks = $project->scheduledTasks()
            ->with('runs')
            ->paginate(15);

        return view('scheduled-tasks.index', compact('project', 'scheduledTasks'));
    }

    /**
     * Show the form for creating a new scheduled task.
     */
    public function create(Project $project): View
    {
        $this->authorize('create', [ScheduledTask::class, $project]);

        return view('scheduled-tasks.create', compact('project'));
    }

    /**
     * Store a newly created scheduled task.
     */
    public function store(StoreScheduledTaskRequest $request, Project $project): RedirectResponse
    {
        $data = $request->safe()->except('enabled');
        $data['enabled'] = $request->boolean('enabled', true);

        $scheduledTask = $project->scheduledTasks()->create($data);

        $scheduledTask->update(['next_run_at' => $scheduledTask->calculateNextRun()]);

        return redirect()->route('projects.scheduled-tasks.show', [$project, $scheduledTask])
            ->with('status', 'Scheduled task created successfully.');
    }

    /**
     * Display the scheduled task with its run history.
     */
    public function show(Project $project, ScheduledTask $scheduledTask): View
    {
        $this->authorize('view', $scheduledTask);

        $runs = $scheduledTask->runs()->paginate(15);

        return view('scheduled-tasks.show', compact('project', 'scheduledTask', 'runs'));
    }

    /**
     * Show the form for editing a scheduled task.
     */
    public function edit(Project $project, ScheduledTask $scheduledTask): View
    {
        $this->authorize('update', $scheduledTask);

        return view('scheduled-tasks.edit', compact('project', 'scheduledTask'));
    }

    /**
     * Update the scheduled task.
     */
    public function update(UpdateScheduledTaskRequest $request, Project $project, ScheduledTask $scheduledTask): RedirectResponse
    {
        $scheduledTask->update($request->safe()->all());

        if ($request->has('cron_expression')) {
            $scheduledTask->update(['next_run_at' => $scheduledTask->calculateNextRun()]);
        }

        return redirect()->route('projects.scheduled-tasks.show', [$project, $scheduledTask])
            ->with('status', 'Scheduled task updated successfully.');
    }

    /**
     * Delete the scheduled task.
     */
    public function destroy(Project $project, ScheduledTask $scheduledTask): RedirectResponse
    {
        $this->authorize('delete', $scheduledTask);

        $scheduledTask->delete();

        return redirect()->route('projects.scheduled-tasks.index', $project)
            ->with('status', 'Scheduled task deleted.');
    }

    /**
     * Record a run for a scheduled task.
     */
    public function recordRun(RecordTaskRunRequest $request, Project $project, ScheduledTask $scheduledTask): RedirectResponse
    {
        $scheduledTask->recordRun($request->validated());

        return redirect()->back()->with('status', 'Task run recorded.');
    }
}
