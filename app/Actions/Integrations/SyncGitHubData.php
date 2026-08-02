<?php

namespace App\Actions\Integrations;

use App\Models\GitHubIntegration;
use App\Services\GitHub\GitHubApiService;
use Illuminate\Support\Carbon;

class SyncGitHubData
{
    public function __construct(private readonly GitHubApiService $api) {}

    /**
     * Refresh the commit and pull request snapshots for an integration.
     */
    public function handle(GitHubIntegration $integration): void
    {
        $token = $integration->access_token;

        if (! $token) {
            return;
        }

        $owner = $integration->repository_owner;
        $repo = $integration->repository_name;

        foreach ($this->api->commits($token, $owner, $repo) as $commit) {
            $integration->commits()->updateOrCreate(
                ['sha' => $commit->sha],
                [
                    'message' => $commit->message,
                    'author_name' => $commit->authorName,
                    'author_email' => $commit->authorEmail,
                    'authored_at' => $commit->authoredAt ? Carbon::parse($commit->authoredAt) : null,
                    'url' => $commit->url,
                ]
            );
        }

        foreach ($this->api->pullRequests($token, $owner, $repo, state: 'all') as $pullRequest) {
            $integration->pullRequests()->updateOrCreate(
                ['number' => $pullRequest->number],
                [
                    'title' => $pullRequest->title,
                    'body' => $pullRequest->body,
                    'state' => $pullRequest->state,
                    'author_name' => $pullRequest->authorName,
                    'opened_at' => $pullRequest->openedAt ? Carbon::parse($pullRequest->openedAt) : null,
                    'merged_at' => $pullRequest->mergedAt ? Carbon::parse($pullRequest->mergedAt) : null,
                    'closed_at' => $pullRequest->closedAt ? Carbon::parse($pullRequest->closedAt) : null,
                    'url' => $pullRequest->url,
                ]
            );
        }

        $integration->forceFill(['last_synced_at' => now()])->save();
    }
}
