<?php

namespace App\Http\Controllers\Web;

use App\Actions\Services\PingServiceHealth;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display a listing of services for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('view', $project);

        $services = $project->services()->with('healthCheckResults')->latest()->get();

        return view('services.index', compact('project', 'services'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create(Project $project): View
    {
        $this->authorize('update', $project);

        return view('services.create', compact('project'));
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(StoreServiceRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $service = $project->services()->create($request->validated());

        return redirect()->route('projects.services.show', [$project, $service]);
    }

    /**
     * Display the specified service dashboard.
     */
    public function show(Project $project, Service $service): View
    {
        $this->authorize('view', $service);

        $results = $service->healthCheckResults()->take(50)->get();

        return view('services.show', compact('project', 'service', 'results'));
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Project $project, Service $service): View
    {
        $this->authorize('update', $service);

        return view('services.edit', compact('project', 'service'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(UpdateServiceRequest $request, Project $project, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $service->update($request->validated());

        return redirect()->route('projects.services.show', [$project, $service]);
    }

    /**
     * Trigger an instant on-demand ping check for the service.
     */
    public function ping(Project $project, Service $service, PingServiceHealth $pingServiceHealth): RedirectResponse
    {
        $this->authorize('update', $service);

        $pingServiceHealth->handle($service);

        return redirect()->back()->with('status', 'Health check dispatched!');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Project $project, Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return redirect()->route('projects.services.index', $project);
    }
}
