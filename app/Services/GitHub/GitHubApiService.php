<?php

namespace App\Services\GitHub;

use App\Services\GitHub\Data\GitHubCommitData;
use App\Services\GitHub\Data\GitHubPullRequestData;
use App\Services\GitHub\Exceptions\GitHubApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GitHubApiService
{
    /**
     * Create a configured GitHub API client for the given token.
     */
    protected function client(string $token): PendingRequest
    {
        return Http::baseUrl(config('oblok.github.api_url'))
            ->withToken($token)
            ->accept('application/vnd.github+json')
            ->asJson()
            ->timeout((int) config('oblok.github.timeout', 10));
    }

    /**
     * Fetch repository metadata.
     *
     * @return array<string, mixed>
     */
    public function repository(string $token, string $owner, string $repo): array
    {
        return $this->request($this->client($token)->get("repos/{$owner}/{$repo}"));
    }

    /**
     * Fetch the default branch of a repository.
     */
    public function defaultBranch(string $token, string $owner, string $repo): ?string
    {
        $data = $this->repository($token, $owner, $repo);

        return isset($data['default_branch']) && is_string($data['default_branch'])
            ? $data['default_branch']
            : null;
    }

    /**
     * Fetch recent commits for the repository.
     *
     * @return array<int, GitHubCommitData>
     */
    public function commits(string $token, string $owner, string $repo, int $perPage = 30): array
    {
        return array_map(
            static fn (array $payload) => GitHubCommitData::fromPayload($payload),
            array_values($this->request(
                $this->client($token)->get("repos/{$owner}/{$repo}/commits", ['per_page' => $perPage])
            ))
        );
    }

    /**
     * Fetch pull requests for the repository.
     *
     * @return array<int, GitHubPullRequestData>
     */
    public function pullRequests(string $token, string $owner, string $repo, string $state = 'open', int $perPage = 30): array
    {
        return array_map(
            static fn (array $payload) => GitHubPullRequestData::fromPayload($payload),
            array_values($this->request(
                $this->client($token)->get("repos/{$owner}/{$repo}/pulls", [
                    'state' => $state,
                    'per_page' => $perPage,
                ])
            ))
        );
    }

    /**
     * Decode a successful response, wrapping transport and API failures as a domain exception.
     *
     * @return array<string, mixed>
     */
    protected function request(Response $response): array
    {
        try {
            if (! $response->successful()) {
                throw new GitHubApiException('GitHub API returned status '.$response->status().': '.$response->body());
            }

            return $response->json();
        } catch (ConnectionException|RequestException $e) {
            throw new GitHubApiException('GitHub API request failed: '.$e->getMessage(), 0, $e);
        }
    }
}
