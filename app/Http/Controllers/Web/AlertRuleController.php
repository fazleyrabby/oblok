<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlertRuleRequest;
use App\Http\Requests\UpdateAlertRuleRequest;
use App\Models\AlertRule;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AlertRuleController extends Controller
{
    /**
     * Display a listing of alert rules for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [AlertRule::class, $project]);

        $alertRules = $project->alertRules()->with('channels')->latest()->get();

        $projects = $this->accessibleProjects();

        return view('alert-rules.index', compact('projects', 'project', 'alertRules'));
    }

    /**
     * Show the form for creating a new alert rule.
     */
    public function create(Project $project): View
    {
        $this->authorize('create', [AlertRule::class, $project]);

        $channels = $project->notificationChannels;

        return view('alert-rules.create', compact('project', 'channels'));
    }

    /**
     * Store a newly created alert rule.
     */
    public function store(StoreAlertRuleRequest $request, Project $project): RedirectResponse
    {
        $alertRule = $project->alertRules()->create($request->safe()->except('channel_ids'));

        if ($request->filled('channel_ids')) {
            $alertRule->channels()->sync($request->input('channel_ids'));
        }

        return redirect()->route('projects.alert-rules.index', $project)
            ->with('status', 'Alert rule created successfully.');
    }

    /**
     * Show the form for editing an alert rule.
     */
    public function edit(Project $project, AlertRule $alertRule): View
    {
        $this->authorize('update', $alertRule);

        $channels = $project->notificationChannels;

        return view('alert-rules.edit', compact('project', 'alertRule', 'channels'));
    }

    /**
     * Update the alert rule.
     */
    public function update(UpdateAlertRuleRequest $request, Project $project, AlertRule $alertRule): RedirectResponse
    {
        $alertRule->update($request->safe()->except('channel_ids'));

        if ($request->has('channel_ids')) {
            $alertRule->channels()->sync($request->input('channel_ids', []));
        }

        return redirect()->route('projects.alert-rules.index', $project)
            ->with('status', 'Alert rule updated successfully.');
    }

    /**
     * Delete the alert rule.
     */
    public function destroy(Project $project, AlertRule $alertRule): RedirectResponse
    {
        $this->authorize('delete', $alertRule);

        $alertRule->delete();

        return redirect()->route('projects.alert-rules.index', $project)
            ->with('status', 'Alert rule deleted.');
    }
}
