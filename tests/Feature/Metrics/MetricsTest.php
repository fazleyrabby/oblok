<?php

use App\Enums\ProjectRole;
use App\Jobs\ScrapeAllMetricTargetsJob;
use App\Jobs\ScrapeMetricTargetJob;
use App\Models\ApiKey;
use App\Models\MetricSample;
use App\Models\MetricTarget;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('owner can ingest metrics via the API', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.metrics.store', $project), [
        'metrics' => [
            ['name' => 'http_requests_total', 'value' => 42, 'labels' => ['code' => '200']],
        ],
    ])->assertCreated()->assertJsonPath('ingested', 1);

    $this->assertDatabaseHas('metric_samples', [
        'project_id' => $project->id,
        'name' => 'http_requests_total',
    ]);
});

test('operator can ingest metrics but viewer cannot', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);

    $this->actingAs($operator)->postJson(route('api.v1.projects.metrics.store', $project), [
        'metrics' => [['name' => 'foo', 'value' => 1]],
    ])->assertCreated();

    $this->actingAs($viewer)->postJson(route('api.v1.projects.metrics.store', $project), [
        'metrics' => [['name' => 'foo', 'value' => 1]],
    ])->assertForbidden();
});

test('bearer api key can ingest metrics', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $token = 'atl_'.str_repeat('y', 36);
    ApiKey::factory()->create([
        'user_id' => $owner->id,
        'project_id' => $project->id,
        'token' => hash('sha256', $token),
    ]);

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson(route('api.v1.projects.metrics.store', $project), [
            'metrics' => [['name' => 'cpu_usage', 'value' => 0.5]],
        ])->assertCreated();

    $this->assertDatabaseCount('metric_samples', 1);
});

test('invalid metrics payload is rejected', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.metrics.store', $project), [
        'metrics' => [
            ['value' => 'not-a-number'],
        ],
    ])->assertUnprocessable();

    $this->assertDatabaseCount('metric_samples', 0);
});

test('member can view the metrics dashboard', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    MetricSample::factory()->create(['project_id' => $project->id, 'name' => 'cpu_usage']);

    $this->actingAs($viewer)->get(route('projects.metrics.index', $project))
        ->assertOk()
        ->assertSee('cpu_usage');
});

test('non-member cannot view the metrics dashboard', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)->get(route('projects.metrics.index', $project))
        ->assertForbidden();
});

test('dashboard data endpoint returns bucketed chart series', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    MetricSample::factory()->create([
        'project_id' => $project->id,
        'name' => 'temperature',
        'value' => 21,
        'recorded_at' => now()->subMinutes(5),
    ]);

    $from = urlencode(now()->subHour()->toIso8601String());
    $to = urlencode(now()->toIso8601String());

    $this->actingAs($owner)->getJson(route('projects.metrics.data', $project)."?name=temperature&from={$from}&to={$to}")
        ->assertOk()
        ->assertJsonPath('name', 'temperature')
        ->assertJsonCount(1, 'series');
});

test('owner can add and remove scrape targets via web', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->post(route('projects.metrics.targets.store', $project), [
        'name' => 'node exporter',
        'url' => 'http://10.0.0.5:9100/metrics',
    ])->assertRedirect()->assertSessionHas('status');

    $this->assertDatabaseCount('metric_targets', 1);

    $target = MetricTarget::first();

    $this->actingAs($owner)->delete(route('projects.metrics.targets.destroy', [$project, $target]))
        ->assertRedirect();

    $this->assertDatabaseCount('metric_targets', 0);
});

test('viewer cannot manage scrape targets', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    $target = MetricTarget::factory()->create(['project_id' => $project->id]);

    $this->actingAs($viewer)->delete(route('projects.metrics.targets.destroy', [$project, $target]))
        ->assertForbidden();
});

test('scrape target job scrapes and the scheduler dispatches all targets', function () {
    Queue::fake();

    $project = Project::factory()->create();
    $enabled = MetricTarget::factory()->create(['project_id' => $project->id, 'enabled' => true]);
    MetricTarget::factory()->create(['project_id' => $project->id, 'enabled' => false]);

    app(ScrapeAllMetricTargetsJob::class)->handle();

    Queue::assertPushed(ScrapeMetricTargetJob::class, 1);
    Queue::assertPushed(ScrapeMetricTargetJob::class, fn ($job) => $job->targetId === $enabled->id);
});

test('API can list, create, and delete scrape targets', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->postJson(route('api.v1.projects.metrics.targets.store', $project), [
        'name' => 'cadvisor',
        'url' => 'http://10.0.0.5:8080/metrics',
    ])->assertCreated()->assertJsonPath('data.name', 'cadvisor');

    $this->actingAs($owner)->getJson(route('api.v1.projects.metrics.targets.index', $project))
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $target = MetricTarget::first();

    $this->actingAs($owner)->deleteJson(route('api.v1.projects.metrics.targets.destroy', [$project, $target]))
        ->assertOk();

    $this->assertDatabaseCount('metric_targets', 0);
});
