<?php

use App\Enums\ProjectRole;
use App\Enums\TaskRunStatus;
use App\Jobs\CheckScheduledTasksJob;
use App\Models\Project;
use App\Models\ScheduledTask;
use App\Models\TaskRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can create a scheduled task via web and next run is computed', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.scheduled-tasks.store', $project), [
        'name' => 'Nightly Backup',
        'command' => 'php artisan backup:run',
        'cron_expression' => '0 2 * * *',
        'timezone' => 'UTC',
        'enabled' => 1,
    ]);

    $response->assertRedirect();

    $task = ScheduledTask::where('project_id', $project->id)->first();

    expect($task)->not->toBeNull()
        ->and($task->name)->toBe('Nightly Backup')
        ->and($task->enabled)->toBeTrue()
        ->and($task->next_run_at)->not->toBeNull();
});

test('invalid cron expression is rejected', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->from(route('projects.scheduled-tasks.create', $project))
        ->post(route('projects.scheduled-tasks.store', $project), [
            'name' => 'Broken',
            'command' => 'php artisan x',
            'cron_expression' => 'not a cron',
            'timezone' => 'UTC',
        ])->assertRedirect()
        ->assertSessionHasErrors('cron_expression');

    $this->assertDatabaseCount('scheduled_tasks', 0);
});

test('viewer cannot create a scheduled task', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);

    $this->actingAs($viewer)->post(route('projects.scheduled-tasks.store', $project), [
        'name' => 'Nope',
        'command' => 'php artisan x',
        'cron_expression' => '*/5 * * * *',
        'timezone' => 'UTC',
    ])->assertForbidden();
});

test('member can view scheduled tasks and run history', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => ProjectRole::Viewer->value]);
    $task = ScheduledTask::factory()->create(['project_id' => $project->id]);
    TaskRun::factory()->count(2)->create(['scheduled_task_id' => $task->id]);

    $this->actingAs($member)->get(route('projects.scheduled-tasks.index', $project))
        ->assertOk()
        ->assertSee($task->name);

    $this->actingAs($member)->get(route('projects.scheduled-tasks.show', [$project, $task]))
        ->assertOk()
        ->assertSee('Run History');
});

test('non-member cannot view scheduled tasks', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    ScheduledTask::factory()->create(['project_id' => $project->id]);

    $this->actingAs($stranger)->get(route('projects.scheduled-tasks.index', $project))
        ->assertForbidden();
});

test('operator can record a run via API which advances the schedule', function () {
    $owner = User::factory()->create();
    $operator = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($operator->id, ['role' => ProjectRole::Operator->value]);
    $task = ScheduledTask::factory()->create([
        'project_id' => $project->id,
        'cron_expression' => '*/5 * * * *',
        'next_run_at' => now()->addMinutes(5),
    ]);

    $response = $this->actingAs($operator)->postJson(route('api.v1.projects.scheduled-tasks.runs', [$project, $task]), [
        'status' => 'success',
        'duration_ms' => 2500,
        'exit_code' => 0,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'success')
        ->assertJsonPath('data.duration_ms', 2500);

    expect($task->fresh()->last_run_at)->not->toBeNull()
        ->and($task->fresh()->next_run_at->isAfter(now()))->toBeTrue();
});

test('viewer cannot record a run', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => ProjectRole::Viewer->value]);
    $task = ScheduledTask::factory()->create(['project_id' => $project->id]);

    $this->actingAs($viewer)->postJson(route('api.v1.projects.scheduled-tasks.runs', [$project, $task]), [
        'status' => 'success',
    ])->assertForbidden();

    $this->assertDatabaseCount('task_runs', 0);
});

test('scheduled job flags overdue tasks as missed', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $overdue = ScheduledTask::factory()->create([
        'project_id' => $project->id,
        'next_run_at' => now()->subMinutes(30),
    ]);
    $healthy = ScheduledTask::factory()->create([
        'project_id' => $project->id,
        'next_run_at' => now()->addMinutes(5),
    ]);

    CheckScheduledTasksJob::dispatchSync();

    expect($overdue->fresh()->runs()->where('status', TaskRunStatus::Missed->value)->count())->toBe(1)
        ->and($overdue->fresh()->next_run_at->isAfter(now()))->toBeTrue()
        ->and($healthy->fresh()->runs()->count())->toBe(0);
});

test('paused tasks are not flagged as missed', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    ScheduledTask::factory()->create([
        'project_id' => $project->id,
        'enabled' => false,
        'next_run_at' => now()->subMinutes(30),
    ]);

    CheckScheduledTasksJob::dispatchSync();

    $this->assertDatabaseCount('task_runs', 0);
});

test('owner can update and delete a scheduled task', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $task = ScheduledTask::factory()->create([
        'project_id' => $project->id,
        'cron_expression' => '0 2 * * *',
    ]);

    $this->actingAs($owner)->put(route('projects.scheduled-tasks.update', [$project, $task]), [
        'name' => 'Updated Task',
        'command' => 'php artisan updated:run',
        'cron_expression' => '30 3 * * *',
        'timezone' => 'UTC',
        'enabled' => 0,
    ])->assertRedirect(route('projects.scheduled-tasks.show', [$project, $task]));

    expect($task->fresh()->name)->toBe('Updated Task')
        ->and($task->fresh()->enabled)->toBeFalse();

    $this->actingAs($owner)->delete(route('projects.scheduled-tasks.destroy', [$project, $task]))
        ->assertRedirect(route('projects.scheduled-tasks.index', $project));

    $this->assertDatabaseMissing('scheduled_tasks', ['id' => $task->id]);
});

test('API exposes scheduled tasks as resources', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    ScheduledTask::factory()->count(2)->create(['project_id' => $project->id]);

    $this->actingAs($owner)->getJson(route('api.v1.projects.scheduled-tasks.index', $project))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure(['data' => [['id', 'name', 'cron_expression', 'enabled', 'next_run_at']]]);
});
