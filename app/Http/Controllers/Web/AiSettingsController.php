<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiSettingsController extends Controller
{
    /**
     * Display a listing of custom AI providers for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('update', $project);

        $providers = $project->aiProviders()->get();
        $projects = $this->accessibleProjects();

        return view('ai-settings.index', compact('projects', 'project', 'providers'));
    }

    /**
     * Store a newly created AI provider in the database.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'endpoint' => 'required|url|max:255',
            'api_key' => 'nullable|string',
            'models' => 'required|string',
            'timeout' => 'required|integer|min:5|max:300',
        ]);

        // Parse comma-separated models list
        $modelsArray = array_filter(
            array_map('trim', explode(',', $validated['models'])),
            fn($val) => $val !== ''
        );

        $project->aiProviders()->create([
            'name' => $validated['name'],
            'endpoint' => $validated['endpoint'],
            'api_key' => $request->filled('api_key') ? $validated['api_key'] : null,
            'models' => array_values($modelsArray),
            'timeout' => $validated['timeout'],
        ]);

        return redirect()->route('projects.ai-settings.index', $project)
            ->with('status', 'AI Provider added successfully.');
    }

    /**
     * Remove the specified AI provider from the database.
     */
    public function destroy(Project $project, AiProvider $aiProvider): RedirectResponse
    {
        $this->authorize('update', $project);

        if ($aiProvider->project_id !== $project->id) {
            abort(403);
        }

        $aiProvider->delete();

        return redirect()->route('projects.ai-settings.index', $project)
            ->with('status', 'AI Provider removed successfully.');
    }
}
