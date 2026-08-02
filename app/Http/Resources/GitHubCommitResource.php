<?php

namespace App\Http\Resources;

use App\Models\GitHubCommit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GitHubCommit
 */
class GitHubCommitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sha' => $this->sha,
            'short_sha' => substr($this->sha, 0, 7),
            'message' => $this->message,
            'author_name' => $this->author_name,
            'author_email' => $this->author_email,
            'authored_at' => $this->authored_at?->toIso8601String(),
            'url' => $this->url,
        ];
    }
}
