<?php

namespace App\Http\Resources;

use App\Models\GitHubPullRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GitHubPullRequest
 */
class GitHubPullRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'title' => $this->title,
            'state' => $this->state,
            'author_name' => $this->author_name,
            'opened_at' => $this->opened_at?->toIso8601String(),
            'merged_at' => $this->merged_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'url' => $this->url,
        ];
    }
}
