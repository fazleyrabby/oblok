<?php

namespace App\Services\GitHub\Data;

final class GitHubCommitData
{
    public function __construct(
        public readonly string $sha,
        public readonly string $message,
        public readonly string $authorName,
        public readonly ?string $authorEmail,
        public readonly ?string $authoredAt,
        public readonly ?string $url,
    ) {}

    /**
     * Build a commit data object from a GitHub API payload.
     *
     * @param  array<array-key, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $commit = $payload['commit'] ?? $payload;
        $author = is_array($commit) ? ($commit['author'] ?? []) : [];

        return new self(
            sha: (string) ($payload['sha'] ?? ''),
            message: (string) (is_array($commit) ? ($commit['message'] ?? '') : ''),
            authorName: (string) (is_array($author) ? ($author['name'] ?? 'Unknown') : 'Unknown'),
            authorEmail: isset($author['email']) ? (string) $author['email'] : null,
            authoredAt: isset($author['date']) ? (string) $author['date'] : null,
            url: isset($payload['html_url']) ? (string) $payload['html_url'] : null,
        );
    }
}
