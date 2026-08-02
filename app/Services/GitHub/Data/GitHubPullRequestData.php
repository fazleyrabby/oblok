<?php

namespace App\Services\GitHub\Data;

final class GitHubPullRequestData
{
    public function __construct(
        public readonly int $number,
        public readonly string $title,
        public readonly ?string $body,
        public readonly string $state,
        public readonly string $authorName,
        public readonly ?string $openedAt,
        public readonly ?string $mergedAt,
        public readonly ?string $closedAt,
        public readonly ?string $url,
    ) {}

    /**
     * Build a pull request data object from a GitHub API payload.
     *
     * @param  array<array-key, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $user = $payload['user'] ?? [];

        return new self(
            number: (int) ($payload['number'] ?? 0),
            title: (string) ($payload['title'] ?? ''),
            body: isset($payload['body']) ? (string) $payload['body'] : null,
            state: (string) ($payload['state'] ?? 'open'),
            authorName: is_array($user) ? (string) ($user['login'] ?? 'Unknown') : 'Unknown',
            openedAt: isset($payload['created_at']) ? (string) $payload['created_at'] : null,
            mergedAt: isset($payload['merged_at']) ? (string) $payload['merged_at'] : null,
            closedAt: isset($payload['closed_at']) ? (string) $payload['closed_at'] : null,
            url: isset($payload['html_url']) ? (string) $payload['html_url'] : null,
        );
    }
}
