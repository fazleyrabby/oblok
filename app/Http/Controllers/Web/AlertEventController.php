<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AlertEvent;
use App\Models\Project;
use Illuminate\View\View;

class AlertEventController extends Controller
{
    /**
     * Display the alerts center for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [AlertEvent::class, $project]);

        $events = $project->alertEvents()
            ->with('alertRule', 'deliveries')
            ->latest('triggered_at')
            ->paginate(15);

        return view('alerts.index', compact('project', 'events'));
    }

    /**
     * Display a single alert event with its deliveries.
     */
    public function show(Project $project, AlertEvent $alertEvent): View
    {
        $this->authorize('view', $alertEvent);

        $alertEvent->load('alertRule', 'deliveries');

        return view('alerts.show', compact('project', 'alertEvent'));
    }
}
