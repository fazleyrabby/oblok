<?php

namespace App\Http\Controllers\Web;

use App\Actions\AiAssistant\SuggestIncidentActions;
use App\Actions\Incidents\CreateIncident;
use App\Actions\Incidents\ResolveIncident;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Models\Incident;
use App\Models\Project;
use App\Services\AiAssistant\Exceptions\AiProviderException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IncidentController extends Controller
{
    /**
     * Display operational incidents timeline for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [Incident::class, $project]);

        $incidents = $project->incidents()->paginate(15);

        $projects = $this->accessibleProjects();

        return view('incidents.index', compact('projects', 'project', 'incidents'));
    }

    /**
     * Show incident creation form.
     */
    public function create(Project $project): View
    {
        $this->authorize('create', [Incident::class, $project]);

        $services = $project->services;

        return view('incidents.create', compact('project', 'services'));
    }

    /**
     * Store new operational incident.
     */
    public function store(StoreIncidentRequest $request, Project $project, CreateIncident $createIncident): RedirectResponse
    {
        $incident = $createIncident->handle($project, $request->validated());

        return redirect()->route('projects.incidents.show', [$project, $incident])
            ->with('status', 'Incident logged successfully.');
    }

    /**
     * Display incident details.
     */
    public function show(Project $project, Incident $incident): View
    {
        $this->authorize('view', $incident);

        return view('incidents.show', compact('project', 'incident'));
    }

    /**
     * Mark an incident as resolved.
     */
    public function resolve(Project $project, Incident $incident, ResolveIncident $resolveIncident): RedirectResponse
    {
        $this->authorize('update', $incident);

        $resolveIncident->handle($incident);

        return redirect()->route('projects.incidents.show', [$project, $incident])
            ->with('status', 'Incident marked as resolved.');
    }

    /**
     * Generate an AI root-cause hypothesis and next steps for an incident.
     */
    public function suggest(Project $project, Incident $incident, SuggestIncidentActions $suggest): JsonResponse
    {
        $this->authorize('view', $incident);

        try {
            $suggestion = $suggest->handle($project, $incident);
        } catch (AiProviderException $e) {
            return response()->json([
                'message' => 'The AI assistant could not be reached.',
                'error' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'data' => [
                'suggestion' => $suggestion,
            ],
        ]);
    }
}
