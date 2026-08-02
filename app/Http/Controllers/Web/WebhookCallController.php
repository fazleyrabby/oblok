<?php

namespace App\Http\Controllers\Web;

use App\Actions\Webhooks\ReplayWebhook;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WebhookCall;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class WebhookCallController extends Controller
{
    /**
     * Display a paginated list of webhook calls for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [WebhookCall::class, $project]);

        $webhookCalls = $project->webhookCalls()
            ->paginate(15);

        $projects = $this->accessibleProjects();

        return view('webhooks.index', compact('projects', 'project', 'webhookCalls'));
    }

    /**
     * Display a single webhook call with its captured payload.
     */
    public function show(Project $project, WebhookCall $webhookCall): View
    {
        $this->authorize('view', $webhookCall);

        return view('webhooks.show', compact('project', 'webhookCall'));
    }

    /**
     * Replay a captured webhook call through its registered processor.
     */
    public function replay(Project $project, WebhookCall $webhookCall, ReplayWebhook $replayWebhook): RedirectResponse
    {
        $this->authorize('replay', $webhookCall);

        try {
            $replayWebhook->handle($webhookCall);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['replay' => $e->getMessage()]);
        }

        return redirect()->back()->with('status', 'Webhook replayed successfully.');
    }
}
