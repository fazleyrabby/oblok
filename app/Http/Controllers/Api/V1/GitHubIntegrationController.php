<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Integrations\ConnectGitHubIntegration;
use App\Actions\Integrations\DisconnectGitHubIntegration;
use App\Actions\Integrations\SyncGitHubData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGitHubIntegrationRequest;
use App\Http\Resources\GitHubCommitResource;
use App\Http\Resources\GitHubIntegrationResource;
use App\Http\Resources\GitHubPullRequestResource;
use App\Models\GitHubIntegration;
use App\Models\Project;
use App\Services\GitHub\Exceptions\GitHubApiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GitHubIntegrationController extends Controller
{
    /**
     * Display the GitHub integration and its repository context.
     */
    public function index(Project $project): GitHubIntegrationResource|JsonResponse
    {
        $this->authorize('viewAny', [GitHubIntegration::class, $project]);

        $integration = $project->githubIntegration;

        if (! $integration) {
            return response()->json(['data' => null]);
        }

        $integration->load(['commits' => fn ($query) => $query->limit(15), 'pullRequests' => fn ($query) => $query->limit(15)]);

        return new GitHubIntegrationResource($integration);
    }

    /**
     * Connect a GitHub repository to the project.
     */
    public function store(
        StoreGitHubIntegrationRequest $request,
        Project $project,
        ConnectGitHubIntegration $connect
    ): GitHubIntegrationResource|JsonResponse {
        $parts = $request->repositoryParts();

        try {
            $integration = $connect->handle(
                $project,
                $parts['owner'],
                $parts['name'],
                $request->validated('access_token')
            );
        } catch (GitHubApiException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new GitHubIntegrationResource($integration);
    }

    /**
     * List captured commits for the integration.
     */
    public function commits(Request $request, Project $project): AnonymousResourceCollection
    {
        $integration = $this->integrationOrAbort($project);

        return GitHubCommitResource::collection(
            $integration->commits()->paginate($request->integer('per_page', 15))
        );
    }

    /**
     * List captured pull requests for the integration.
     */
    public function pullRequests(Request $request, Project $project): AnonymousResourceCollection
    {
        $integration = $this->integrationOrAbort($project);

        $state = $request->string('state', 'open')->toString();

        return GitHubPullRequestResource::collection(
            $integration->pullRequests()
                ->state(in_array($state, ['open', 'closed', 'all'], true) ? $state : 'open')
                ->paginate($request->integer('per_page', 15))
        );
    }

    /**
     * Trigger an immediate sync of the repository data.
     */
    public function sync(Project $project, SyncGitHubData $sync): JsonResponse
    {
        $integration = $this->integrationOrAbort($project);

        $this->authorize('sync', $integration);

        try {
            $sync->handle($integration);
        } catch (GitHubApiException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Repository data synchronized successfully.']);
    }

    /**
     * Disconnect the GitHub integration from the project.
     */
    public function destroy(Project $project, DisconnectGitHubIntegration $disconnect): JsonResponse
    {
        $integration = $this->integrationOrAbort($project);

        $this->authorize('delete', $integration);

        $disconnect->handle($integration);

        return response()->json(['message' => 'GitHub integration disconnected.']);
    }

    /**
     * Resolve the project's integration or abort with a 404 response.
     */
    private function integrationOrAbort(Project $project): GitHubIntegration
    {
        $this->authorize('viewAny', [GitHubIntegration::class, $project]);

        $integration = $project->githubIntegration;

        if (! $integration) {
            abort(404);
        }

        return $integration;
    }
}
