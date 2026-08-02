<?php

namespace App\Http\Controllers\Web;

use App\Actions\Integrations\ConnectGitHubIntegration;
use App\Actions\Integrations\DisconnectGitHubIntegration;
use App\Actions\Integrations\SyncGitHubData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGitHubIntegrationRequest;
use App\Models\GitHubIntegration;
use App\Models\Project;
use App\Services\GitHub\Exceptions\GitHubApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GitHubIntegrationController extends Controller
{
    /**
     * Display the GitHub integration overview for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [GitHubIntegration::class, $project]);

        $integration = $project->githubIntegration;

        $commits = $integration?->commits()->limit(15)->get();
        $pullRequests = $integration?->pullRequests()->limit(15)->get();

        return view('github.index', compact('project', 'integration', 'commits', 'pullRequests'));
    }

    /**
     * Connect a GitHub repository to the project.
     */
    public function store(
        StoreGitHubIntegrationRequest $request,
        Project $project,
        ConnectGitHubIntegration $connect
    ): RedirectResponse {
        $parts = $request->repositoryParts();

        try {
            $integration = $connect->handle(
                $project,
                $parts['owner'],
                $parts['name'],
                $request->validated('access_token')
            );
        } catch (GitHubApiException $e) {
            return back()->withErrors(['repository' => $e->getMessage()])->withInput();
        }

        return redirect()->route('projects.github.index', $project)
            ->with('status', "Linked {$integration->repositorySlug()}. Repository data is syncing.");
    }

    /**
     * Trigger an immediate sync of the repository data.
     */
    public function sync(Project $project, SyncGitHubData $sync): RedirectResponse
    {
        $integration = $project->githubIntegration;

        if ($integration) {
            $this->authorize('sync', $integration);
        } else {
            $this->authorize('viewAny', [GitHubIntegration::class, $project]);
        }

        if (! $integration) {
            return back()->with('status', 'No GitHub integration is connected yet.');
        }

        try {
            $sync->handle($integration);
        } catch (GitHubApiException $e) {
            return back()->withErrors(['sync' => $e->getMessage()]);
        }

        return back()->with('status', 'Repository data synchronized successfully.');
    }

    /**
     * Disconnect the GitHub integration from the project.
     */
    public function destroy(Project $project, DisconnectGitHubIntegration $disconnect): RedirectResponse
    {
        $integration = $project->githubIntegration;

        if ($integration) {
            $this->authorize('delete', $integration);
        } else {
            $this->authorize('viewAny', [GitHubIntegration::class, $project]);
        }

        if (! $integration) {
            return back()->with('status', 'No GitHub integration is connected.');
        }

        $disconnect->handle($integration);

        return redirect()->route('projects.github.index', $project)
            ->with('status', 'GitHub integration disconnected.');
    }
}
