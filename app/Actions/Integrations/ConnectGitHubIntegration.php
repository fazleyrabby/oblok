<?php

namespace App\Actions\Integrations;

use App\Jobs\SyncGitHubDataJob;
use App\Models\GitHubIntegration;
use App\Models\Project;
use App\Services\GitHub\Exceptions\GitHubApiException;
use App\Services\GitHub\GitHubApiService;

class ConnectGitHubIntegration
{
    public function __construct(private readonly GitHubApiService $api) {}

    /**
     * Link a project to a GitHub repository and begin syncing commit/PR context.
     *
     * @throws GitHubApiException When the repository cannot be reached.
     */
    public function handle(Project $project, string $owner, string $repo, string $token): GitHubIntegration
    {
        $defaultBranch = $this->api->defaultBranch($token, $owner, $repo);

        $integration = GitHubIntegration::updateOrCreate(
            ['project_id' => $project->id],
            [
                'repository_owner' => $owner,
                'repository_name' => $repo,
                'access_token' => $token,
                'default_branch' => $defaultBranch,
                'enabled' => true,
            ]
        );

        SyncGitHubDataJob::dispatch($integration->id);

        return $integration;
    }
}
