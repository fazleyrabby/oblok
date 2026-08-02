<?php

use App\Enums\TaskRunStatus;
use App\Models\Project;
use App\Models\ScheduledTask;
use App\Models\TaskRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('scheduled task computes next run date from cron expression', function () {
    $task = ScheduledTask::factory()->create([
        'cron_expression' => '*/5 * * * *',
        'last_run_at' => null,
    ]);

    $next = $task->calculateNextRun();

    expect($next->isAfter(now()))->toBeTrue()
        ->and($next->minute % 5)->toBe(0);
});

test('scheduled task computes next run after a given point in time', function () {
    $task = ScheduledTask::factory()->create([
        'cron_expression' => '0 0 * * *',
    ]);

    $from = now()->startOfDay()->addHours(12);
    $next = $task->calculateNextRun($from);

    expect($next->toDateString())->toBe($from->copy()->addDay()->toDateString())
        ->and($next->hour)->toBe(0);
});

test('recording a run creates a task run and advances the schedule', function () {
    $task = ScheduledTask::factory()->create([
        'cron_expression' => '*/5 * * * *',
        'last_run_at' => null,
        'next_run_at' => now()->addMinutes(5),
    ]);

    $run = $task->recordRun([
        'status' => 'success',
        'started_at' => now(),
        'duration_ms' => 1200,
        'exit_code' => 0,
        'output' => 'done',
    ]);

    expect($run)->toBeInstanceOf(TaskRun::class)
        ->and($run->status)->toBe(TaskRunStatus::Success)
        ->and($task->fresh()->last_run_at)->not->toBeNull()
        ->and($task->fresh()->last_run_at->equalTo($run->started_at))->toBeTrue()
        ->and($task->fresh()->next_run_at->isAfter($run->started_at))->toBeTrue();
});

test('marking a task as missed creates a missed run and advances the schedule', function () {
    $task = ScheduledTask::factory()->create([
        'cron_expression' => '*/5 * * * *',
        'next_run_at' => now()->subMinutes(30),
    ]);

    $scheduledAt = $task->next_run_at->copy();

    $run = $task->markMissed();

    expect($run->status)->toBe(TaskRunStatus::Missed)
        ->and($run->started_at->isSameSecond($scheduledAt))->toBeTrue()
        ->and($task->fresh()->next_run_at->isAfter(now()))->toBeTrue();
});

test('overdue scope includes only tasks past the grace window', function () {
    ScheduledTask::factory()->create(['next_run_at' => now()->subMinutes(10)]);
    ScheduledTask::factory()->create(['next_run_at' => now()->subMinutes(1)]);
    ScheduledTask::factory()->create(['next_run_at' => now()->addMinutes(5)]);
    ScheduledTask::factory()->create(['next_run_at' => null]);

    expect(ScheduledTask::overdue()->count())->toBe(1);
});

test('task run casts status and numeric fields', function () {
    $task = ScheduledTask::factory()->create();
    $run = TaskRun::factory()->create([
        'scheduled_task_id' => $task->id,
        'status' => TaskRunStatus::Failed,
        'duration_ms' => 500,
        'exit_code' => 1,
    ]);

    expect($run->status)->toBe(TaskRunStatus::Failed)
        ->and($run->status->isFailed())->toBeTrue()
        ->and($run->duration_ms)->toBe(500)
        ->and($run->scheduledTask->is($task))->toBeTrue();
});

test('task belongs to a project', function () {
    $project = Project::factory()->create();
    $task = ScheduledTask::factory()->create(['project_id' => $project->id]);

    expect($task->project->is($project))->toBeTrue();
});
