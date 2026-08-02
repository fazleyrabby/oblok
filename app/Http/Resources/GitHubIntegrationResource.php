<?php

namespace App\Http\Resources;

use App\Models\GitHubIntegration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GitHubIntegration
 */
class GitHubIntegrationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'repository' => $this->repositorySlug(),
            'repository_owner' => $this->repository_owner,
            'repository_name' => $this->repository_name,
            'default_branch' => $this->default_branch,
            'enabled' => $this->enabled,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'commits' => GitHubCommitResource::collection($this->whenLoaded('commits')),
            'pull_requests' => GitHubPullRequestResource::collection($this->whenLoaded('pullRequests')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
