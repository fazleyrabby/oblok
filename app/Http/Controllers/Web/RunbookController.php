<?php

namespace App\Http\Controllers\Web;

use App\Actions\Runbooks\CreateRunbook;
use App\Actions\Runbooks\ExecuteRunbook;
use App\Actions\Runbooks\UpdateRunbook;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRunbookRequest;
use App\Http\Requests\UpdateRunbookRequest;
use App\Models\Project;
use App\Models\Runbook;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RunbookController extends Controller
{
    /**
     * Display a listing of runbooks for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [Runbook::class, $project]);

        $runbooks = $project->runbooks()
            ->withCount('runs')
            ->latest()
            ->paginate(15);

        $projects = $this->accessibleProjects();

        return view('runbooks.index', compact('projects', 'project', 'runbooks'));
    }

    /**
     * Show the form for creating a new runbook.
     */
    public function create(Project $project): View
    {
        $this->authorize('create', [Runbook::class, $project]);

        return view('runbooks.create', compact('project'));
    }

    /**
     * Store a newly created runbook.
     */
    public function store(StoreRunbookRequest $request, Project $project, CreateRunbook $createRunbook): RedirectResponse
    {
        $validated = $request->validated();
        $config = $this->buildConfigArray($validated);

        $runbook = $createRunbook->handle($project, array_merge($validated, [
            'config' => $config,
            'enabled' => $request->boolean('enabled', true),
        ]));

        return redirect()->route('projects.runbooks.show', [$project, $runbook])
            ->with('status', 'Runbook created successfully.');
    }

    /**
     * Display runbook details and execution history.
     */
    public function show(Project $project, Runbook $runbook): View
    {
        $this->authorize('view', $runbook);

        $runs = $runbook->runs()->paginate(15);

        return view('runbooks.show', compact('project', 'runbook', 'runs'));
    }

    /**
     * Show the form for editing the runbook.
     */
    public function edit(Project $project, Runbook $runbook): View
    {
        $this->authorize('update', $runbook);

        return view('runbooks.edit', compact('project', 'runbook'));
    }

    /**
     * Update the runbook.
     */
    public function update(UpdateRunbookRequest $request, Project $project, Runbook $runbook, UpdateRunbook $updateRunbook): RedirectResponse
    {
        $validated = $request->validated();
        $config = $this->buildConfigArray($validated);

        $updateRunbook->handle($runbook, array_merge($validated, [
            'config' => $config,
            'enabled' => $request->boolean('enabled'),
        ]));

        return redirect()->route('projects.runbooks.show', [$project, $runbook])
            ->with('status', 'Runbook updated successfully.');
    }

    /**
     * Delete the runbook.
     */
    public function destroy(Project $project, Runbook $runbook): RedirectResponse
    {
        $this->authorize('delete', $runbook);

        $runbook->delete();

        return redirect()->route('projects.runbooks.index', $project)
            ->with('status', 'Runbook deleted successfully.');
    }

    /**
     * Manually execute the runbook.
     */
    public function execute(Project $project, Runbook $runbook, ExecuteRunbook $executeRunbook): RedirectResponse
    {
        $this->authorize('execute', $runbook);

        $run = $executeRunbook->handle($runbook, 'manual', auth()->id());

        $statusMessage = $run->status->value === 'successful'
            ? 'Runbook executed successfully.'
            : 'Runbook execution failed. Check run logs for output.';

        return redirect()->route('projects.runbooks.show', [$project, $runbook])
            ->with('status', $statusMessage);
    }

    /**
     * Build config array based on runbook type.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildConfigArray(array $validated): array
    {
        $type = $validated['type'] ?? 'artisan';

        if ($type === 'webhook') {
            return [
                'url' => $validated['url'] ?? '',
                'method' => $validated['method'] ?? 'POST',
                'headers' => $validated['headers'] ?? null,
                'body' => $validated['body'] ?? null,
            ];
        }

        return [
            'command' => $validated['command'] ?? 'cache:clear',
            'parameters' => $validated['parameters'] ?? null,
        ];
    }
}
