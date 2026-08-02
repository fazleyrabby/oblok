<?php

use App\Enums\ProjectRole;
use App\Jobs\SyncGitHubDataJob;
use App\Models\GitHubCommit;
use App\Models\GitHubIntegration;
use App\Models\GitHubPullRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('owner can connect a GitHub repository via web', function () {
    Queue::fake();
    Http::fake([
        'api.github.com/repos/acme/platform' => Http::response(['default_branch' => 'main']),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->post(route('projects.github.store', $project), [
        'repository' => 'acme/platform',
        'access_token' => 'ghp_some-long-token-value',
    ])->assertRedirect(route('projects.github.index', $project));

    $integration = $project->githubIntegration;

    expect($integration)->not->toBeNull()
        ->and($integration->repository_owner)->toBe('acme')
        ->and($integration->repository_name)->toBe('platform')
        ->and($integration->default_branch)->toBe('main')
        ->and($integration->getRawOriginal('access_token'))->not->toBe('ghp_some-long-token-value');

    Queue::assertPushed(SyncGitHubDataJob::class);
});

test('malformed repository is rejected', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->post(route('projects.github.store', $project), [
        'repository' => 'not-a-repo',
        'access_token' => 'ghp_some-long-token-value',
    ])->assertSessionHasErrors('repository');

    $this->assertDatabaseCount('github_integrations', 0);
});

test('operator cannot connect a GitHub integration', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);

    $this->actingAs($operator)->post(route('projects.github.store', $project), [
        'repository' => 'acme/platform',
        'access_token' => 'ghp_some-long-token-value',
    ])->assertForbidden();
});

test('non-member cannot view the GitHub integration page', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)->get(route('projects.github.index', $project))
        ->assertForbidden();
});

test('owner can connect a repository via the API', function () {
    Queue::fake();
    Http::fake([
        'api.github.com/repos/acme/platform' => Http::response(['default_branch' => 'main']),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.github.store', $project), [
        'repository' => 'acme/platform',
        'access_token' => 'ghp_some-long-token-value',
    ])->assertCreated()
        ->assertJsonPath('data.repository', 'acme/platform')
        ->assertJsonPath('data.default_branch', 'main');

    $this->assertDatabaseHas('github_integrations', [
        'project_id' => $project->id,
        'repository_owner' => 'acme',
        'repository_name' => 'platform',
    ]);
});

test('member can view repository context', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => ProjectRole::Viewer->value]);
    $integration = GitHubIntegration::factory()->create([
        'project_id' => $project->id,
        'repository_owner' => 'acme',
        'repository_name' => 'platform',
    ]);
    GitHubCommit::factory()->create(['github_integration_id' => $integration->id, 'message' => 'Fix deploy script']);
    GitHubPullRequest::factory()->create(['github_integration_id' => $integration->id, 'title' => 'Add monitoring', 'state' => 'open']);

    $this->actingAs($member)->get(route('projects.github.index', $project))
        ->assertOk()
        ->assertSee('acme/platform')
        ->assertSee('Fix deploy script')
        ->assertSee('Add monitoring');
});

test('owner can sync repository data and disconnect', function () {
    Http::fake([
        'api.github.com/repos/acme/platform/commits*' => Http::response([
            ['sha' => 'abc123', 'commit' => ['message' => 'Initial commit', 'author' => ['name' => 'Jane']], 'html_url' => 'https://github.com/acme/platform/commit/abc123'],
        ]),
        'api.github.com/repos/acme/platform/pulls*' => Http::response([
            ['number' => 1, 'title' => 'Add feature', 'state' => 'open', 'user' => ['login' => 'jane'], 'html_url' => 'https://github.com/acme/platform/pull/1'],
        ]),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $integration = GitHubIntegration::factory()->create([
        'project_id' => $project->id,
        'repository_owner' => 'acme',
        'repository_name' => 'platform',
        'access_token' => 'ghp_token',
    ]);

    $this->actingAs($owner)->post(route('projects.github.sync', $project))
        ->assertRedirect()
        ->assertSessionHas('status', 'Repository data synchronized successfully.');

    expect($integration->commits()->count())->toBe(1)
        ->and($integration->pullRequests()->count())->toBe(1)
        ->and($integration->fresh()->last_synced_at)->not->toBeNull();

    $this->actingAs($owner)->delete(route('projects.github.destroy', $project))
        ->assertRedirect(route('projects.github.index', $project));

    $this->assertDatabaseMissing('github_integrations', ['id' => $integration->id])
        ->assertDatabaseCount('github_commits', 0)
        ->assertDatabaseCount('github_pull_requests', 0);
});

test('connect exposes integration via API and commits and pull requests are listed', function () {
    Http::fake([
        'api.github.com/repos/acme/platform' => Http::response(['default_branch' => 'main']),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $integration = GitHubIntegration::factory()->create([
        'project_id' => $project->id,
        'repository_owner' => 'acme',
        'repository_name' => 'platform',
    ]);
    GitHubCommit::factory()->count(2)->create(['github_integration_id' => $integration->id]);
    GitHubPullRequest::factory()->count(1)->create(['github_integration_id' => $integration->id]);

    $this->actingAs($owner)->getJson(route('api.v1.projects.github.index', $project))
        ->assertOk()
        ->assertJsonPath('data.repository', 'acme/platform')
        ->assertJsonCount(2, 'data.commits')
        ->assertJsonCount(1, 'data.pull_requests');

    $this->actingAs($owner)->getJson(route('api.v1.projects.github.commits', $project))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure(['data' => [['sha', 'message', 'author_name']]]);

    $this->actingAs($owner)->getJson(route('api.v1.projects.github.pull-requests', $project))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('unconnected project returns null integration from the API', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->getJson(route('api.v1.projects.github.index', $project))
        ->assertOk()
        ->assertJsonPath('data', null);
});

test('viewer cannot sync or disconnect via API', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    $integration = GitHubIntegration::factory()->create(['project_id' => $project->id]);

    $this->actingAs($viewer)->postJson(route('api.v1.projects.github.sync', $project))
        ->assertForbidden();

    $this->actingAs($viewer)->deleteJson(route('api.v1.projects.github.destroy', $project))
        ->assertForbidden();

    $this->assertDatabaseHas('github_integrations', ['id' => $integration->id]);
});

test('failed sync surfaces a validation error', function () {
    Http::fake([
        'api.github.com/repos/acme/platform/commits*' => Http::response(['message' => 'Bad credentials'], 401),
        'api.github.com/repos/acme/platform/pulls*' => Http::response(['message' => 'Bad credentials'], 401),
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $integration = GitHubIntegration::factory()->create([
        'project_id' => $project->id,
        'repository_owner' => 'acme',
        'repository_name' => 'platform',
        'access_token' => 'ghp_expired',
    ]);

    $this->actingAs($owner)->post(route('projects.github.sync', $project))
        ->assertSessionHasErrors('sync');

    expect($integration->fresh()->last_synced_at)->toBeNull();
});
