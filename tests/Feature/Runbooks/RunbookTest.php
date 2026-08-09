<?php

use App\Actions\Runbooks\CreateRunbook;
use App\Actions\Runbooks\ExecuteRunbook;
use App\Enums\RunbookRunStatus;
use App\Enums\RunbookType;
use App\Events\AlertTriggered;
use App\Events\ServiceStatusChanged;
use App\Jobs\ExecuteRunbookJob;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\Project;
use App\Models\Runbook;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->project = Project::factory()->create(['user_id' => $this->user->id]);
});

test('user can create artisan, webhook, and shell runbooks', function () {
    $createAction = app(CreateRunbook::class);

    $artisanRunbook = $createAction->handle($this->project, [
        'name' => 'Clear Cache',
        'type' => 'artisan',
        'config' => ['command' => 'cache:clear'],
        'trigger_type' => 'both',
        'cooldown_minutes' => 5,
        'timeout_seconds' => 30,
    ]);

    expect($artisanRunbook)->not->toBeNull();
    expect($artisanRunbook->type)->toBe(RunbookType::Artisan);
    expect($artisanRunbook->config['command'])->toBe('cache:clear');

    $webhookRunbook = $createAction->handle($this->project, [
        'name' => 'Restart Webhook',
        'type' => 'webhook',
        'config' => ['url' => 'https://example.com/api/restart', 'method' => 'POST'],
        'trigger_type' => 'automatic',
        'cooldown_minutes' => 10,
    ]);

    expect($webhookRunbook->type)->toBe(RunbookType::Webhook);
    expect($webhookRunbook->config['url'])->toBe('https://example.com/api/restart');
});

test('manual execution runs artisan runbook and records output log', function () {
    $runbook = Runbook::factory()->create([
        'project_id' => $this->project->id,
        'type' => RunbookType::Artisan,
        'config' => ['command' => 'cache:clear'],
        'enabled' => true,
    ]);

    $executeAction = app(ExecuteRunbook::class);
    $run = $executeAction->handle($runbook, 'manual', (string) $this->user->id);

    expect($run)->not->toBeNull();
    expect($run->status)->toBe(RunbookRunStatus::Successful);
    expect($run->exit_code)->toBe(0);
    expect($runbook->fresh()->last_executed_at)->not->toBeNull();
    expect($runbook->fresh()->isInCooldown())->toBeTrue();
});

test('manual execution runs webhook runbook and logs HTTP response', function () {
    Http::fake([
        'example.com/*' => Http::response(['status' => 'restarted'], 200),
    ]);

    $runbook = Runbook::factory()->create([
        'project_id' => $this->project->id,
        'type' => RunbookType::Webhook,
        'config' => ['url' => 'https://example.com/restart', 'method' => 'POST'],
        'enabled' => true,
    ]);

    $executeAction = app(ExecuteRunbook::class);
    $run = $executeAction->handle($runbook, 'manual', (string) $this->user->id);

    expect($run->status)->toBe(RunbookRunStatus::Successful);
    expect($run->exit_code)->toBe(200);
    expect($run->output)->toContain('HTTP 200');
});

test('service health failure triggers automatic self-healing runbook execution', function () {
    Queue::fake();

    $runbook = Runbook::factory()->create([
        'project_id' => $this->project->id,
        'type' => RunbookType::Artisan,
        'config' => ['command' => 'queue:restart'],
        'trigger_type' => 'automatic',
        'enabled' => true,
        'last_executed_at' => null,
    ]);

    $service = Service::factory()->create([
        'project_id' => $this->project->id,
        'status' => 'healthy',
        'is_flapping' => false,
        'runbook_id' => $runbook->id,
    ]);

    // Dispatch ServiceStatusChanged event (failing)
    event(new ServiceStatusChanged($service, 'healthy', 'failing'));

    Queue::assertPushed(ExecuteRunbookJob::class, function ($job) use ($runbook, $service) {
        return $job->runbook->id === $runbook->id
            && $job->triggeredByType === 'service'
            && $job->triggeredById === $service->id;
    });
});

test('cooldown period prevents automatic runbook execution loops', function () {
    Queue::fake();

    $runbook = Runbook::factory()->create([
        'project_id' => $this->project->id,
        'type' => RunbookType::Artisan,
        'config' => ['command' => 'queue:restart'],
        'trigger_type' => 'automatic',
        'enabled' => true,
        'cooldown_minutes' => 10,
        'last_executed_at' => now()->subMinutes(2), // Cooldown active!
    ]);

    $service = Service::factory()->create([
        'project_id' => $this->project->id,
        'status' => 'healthy',
        'is_flapping' => false,
        'runbook_id' => $runbook->id,
    ]);

    event(new ServiceStatusChanged($service, 'healthy', 'failing'));

    // Should NOT be pushed due to active cooldown
    Queue::assertNotPushed(ExecuteRunbookJob::class);
});

test('flapping service failure suppresses automatic self-healing runbook', function () {
    Queue::fake();

    $runbook = Runbook::factory()->create([
        'project_id' => $this->project->id,
        'type' => RunbookType::Artisan,
        'trigger_type' => 'automatic',
        'enabled' => true,
    ]);

    $service = Service::factory()->create([
        'project_id' => $this->project->id,
        'status' => 'healthy',
        'is_flapping' => true, // Flapping!
        'runbook_id' => $runbook->id,
    ]);

    event(new ServiceStatusChanged($service, 'healthy', 'failing'));

    // Should NOT be pushed due to flapping state
    Queue::assertNotPushed(ExecuteRunbookJob::class);
});

test('alert rule trigger fires assigned self-healing runbook', function () {
    Queue::fake();

    $runbook = Runbook::factory()->create([
        'project_id' => $this->project->id,
        'type' => RunbookType::Artisan,
        'trigger_type' => 'both',
        'enabled' => true,
    ]);

    $alertRule = AlertRule::factory()->create([
        'project_id' => $this->project->id,
        'runbook_id' => $runbook->id,
    ]);

    $alertEvent = AlertEvent::create([
        'alert_rule_id' => $alertRule->id,
        'project_id' => $this->project->id,
        'subject' => 'Service Outage',
        'state' => 'firing',
        'triggered_at' => now(),
    ]);

    event(new AlertTriggered($this->project, $alertEvent));

    Queue::assertPushed(ExecuteRunbookJob::class, function ($job) use ($runbook, $alertRule) {
        return $job->runbook->id === $runbook->id
            && $job->triggeredByType === 'alert_rule'
            && $job->triggeredById === $alertRule->id;
    });
});

test('authenticated user can access runbook views via controller', function () {
    $runbook = Runbook::factory()->create([
        'project_id' => $this->project->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('projects.runbooks.index', $this->project))
        ->assertStatus(200)
        ->assertSee($runbook->name);

    $this->actingAs($this->user)
        ->get(route('projects.runbooks.show', [$this->project, $runbook]))
        ->assertStatus(200)
        ->assertSee($runbook->name);
});
