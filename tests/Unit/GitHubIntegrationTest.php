<?php

use App\Actions\Integrations\SyncGitHubData;
use App\Models\GitHubIntegration;
use App\Models\Project;
use App\Services\GitHub\Exceptions\GitHubApiException;
use App\Services\GitHub\GitHubApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('service fetches repository metadata and default branch', function () {
    Http::fake([
        'api.github.com/repos/acme/platform' => Http::response([
            'default_branch' => 'main',
        ]),
    ]);

    $service = app(GitHubApiService::class);

    $branch = $service->defaultBranch('token', 'acme', 'platform');

    expect($branch)->toBe('main');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.github.com/repos/acme/platform'
        && $request->hasHeader('Authorization', 'Bearer token'));
});

test('service fetches commits and pull requests', function () {
    Http::fake([
        'api.github.com/repos/acme/platform/commits*' => Http::response([
            [
                'sha' => 'abc123',
                'commit' => ['message' => 'Fix bug', 'author' => ['name' => 'Jane', 'email' => 'jane@example.com']],
                'html_url' => 'https://github.com/acme/platform/commit/abc123',
            ],
        ]),
        'api.github.com/repos/acme/platform/pulls*' => Http::response([
            [
                'number' => 42,
                'title' => 'Add feature',
                'state' => 'open',
                'user' => ['login' => 'jane'],
                'html_url' => 'https://github.com/acme/platform/pull/42',
            ],
        ]),
    ]);

    $service = app(GitHubApiService::class);

    $commits = $service->commits('token', 'acme', 'platform');
    $pullRequests = $service->pullRequests('token', 'acme', 'platform');

    expect($commits[0]->sha)->toBe('abc123')
        ->and($commits[0]->message)->toBe('Fix bug')
        ->and($commits[0]->authorName)->toBe('Jane')
        ->and($pullRequests[0]->number)->toBe(42)
        ->and($pullRequests[0]->state)->toBe('open');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/commits'));
    Http::assertSent(fn ($request) => str_contains($request->url(), '/pulls')
        && $request->data()['state'] === 'open');
});

test('service propagates api failures for unauthenticated requests', function () {
    Http::fake([
        'api.github.com/repos/acme/platform' => Http::response(['message' => 'Bad credentials'], 401),
    ]);

    $service = app(GitHubApiService::class);

    expect(fn () => $service->repository('bad-token', 'acme', 'platform'))
        ->toThrow(GitHubApiException::class);
});

test('integration encrypts the access token at rest', function () {
    $integration = GitHubIntegration::factory()->create(['access_token' => 'ghp_secret-token']);

    $raw = $integration->getRawOriginal('access_token');

    expect($raw)->not->toBe('ghp_secret-token')
        ->and($integration->access_token)->toBe('ghp_secret-token');
});

test('integration exposes repository slug and project relationship', function () {
    $project = Project::factory()->create();
    $integration = GitHubIntegration::factory()->create([
        'project_id' => $project->id,
        'repository_owner' => 'acme',
        'repository_name' => 'platform',
    ]);

    expect($integration->repositorySlug())->toBe('acme/platform')
        ->and($integration->project->is($project))->toBeTrue()
        ->and($project->githubIntegration->is($integration))->toBeTrue();
});

test('sync action upserts commits and pull requests', function () {
    Http::fake([
        'api.github.com/repos/acme/platform/commits*' => Http::response([
            [
                'sha' => 'abc123',
                'commit' => [
                    'message' => 'Fix bug',
                    'author' => ['name' => 'Jane', 'email' => 'jane@example.com', 'date' => '2026-08-01T10:00:00Z'],
                ],
                'html_url' => 'https://github.com/acme/platform/commit/abc123',
            ],
        ]),
        'api.github.com/repos/acme/platform/pulls*' => Http::response([
            [
                'number' => 42,
                'title' => 'Add feature',
                'state' => 'open',
                'user' => ['login' => 'jane'],
                'created_at' => '2026-08-01T09:00:00Z',
                'merged_at' => null,
                'closed_at' => null,
                'html_url' => 'https://github.com/acme/platform/pull/42',
            ],
        ]),
    ]);

    $integration = GitHubIntegration::factory()->create([
        'repository_owner' => 'acme',
        'repository_name' => 'platform',
        'access_token' => 'ghp_token',
    ]);

    app(SyncGitHubData::class)->handle($integration);

    expect($integration->commits()->count())->toBe(1)
        ->and($integration->pullRequests()->count())->toBe(1)
        ->and($integration->commits()->first()->message)->toBe('Fix bug')
        ->and($integration->pullRequests()->first()->number)->toBe(42)
        ->and($integration->fresh()->last_synced_at)->not->toBeNull();
});
