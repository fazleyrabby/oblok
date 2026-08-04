<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    /**
     * Display the operational AI assistant for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('useAssistant', $project);

        $projects = $this->accessibleProjects();

        return view('ai-assistant.index', compact('projects', 'project'));
    }
}
