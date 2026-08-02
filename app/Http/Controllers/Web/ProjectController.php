<?php

namespace App\Http\Controllers\Web;

use App\Actions\Projects\ArchiveProject;
use App\Actions\Projects\CreateProject;
use App\Actions\Projects\UpdateProject;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): View
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

        $projects = $query->latest()->paginate(12)->withQueryString();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        return view('projects.create');
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request, CreateProject $createProject): RedirectResponse
    {
        $project = $createProject->handle($request->user(), $request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Request $request, Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project, UpdateProject $updateProject): RedirectResponse
    {
        $project = $updateProject->handle($project, $request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Archive or unarchive the specified project.
     */
    public function archive(Request $request, Project $project, ArchiveProject $archiveProject): RedirectResponse
    {
        $this->authorize('update', $project);

        $archive = $request->boolean('archive', true);
        $archiveProject->handle($project, $archive);

        $message = $archive ? 'Project archived successfully.' : 'Project unarchived successfully.';

        return redirect()
            ->route('projects.index')
            ->with('success', $message);
    }

    /**
     * Soft delete the specified project.
     */
    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
