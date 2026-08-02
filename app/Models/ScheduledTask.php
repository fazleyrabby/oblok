<?php

namespace App\Models;

use App\Enums\TaskRunStatus;
use Cron\CronExpression;
use Database\Factories\ScheduledTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $name
 * @property string|null $description
 * @property string $command
 * @property string $cron_expression
 * @property string $timezone
 * @property bool $enabled
 * @property Carbon|null $last_run_at
 * @property Carbon|null $next_run_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'project_id', 'name', 'description', 'command', 'cron_expression',
    'timezone', 'enabled', 'last_run_at', 'next_run_at',
])]
class ScheduledTask extends Model
{
    /** @use HasFactory<ScheduledTaskFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the task.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the runs recorded for this task.
     *
     * @return HasMany<TaskRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(TaskRun::class)->latest('started_at');
    }

    /**
     * Scope a query to only include enabled tasks.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope a query to tasks whose scheduled run window has already passed.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeOverdue(Builder $query, ?Carbon $now = null): Builder
    {
        $now ??= now();

        $graceMinutes = (int) config('atlas.scheduler.missed_grace_minutes', 5);

        return $query->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', $now->copy()->subMinutes($graceMinutes));
    }

    /**
     * Compute the next run date for the cron expression.
     */
    public function calculateNextRun(?Carbon $after = null): Carbon
    {
        $after ??= $this->last_run_at ?? now();

        return Carbon::instance(
            CronExpression::factory($this->cron_expression)->getNextRunDate($after, 0, false, $this->timezone)
        );
    }

    /**
     * Record a completed run and advance the schedule.
     */
    public function recordRun(array $data): TaskRun
    {
        $startedAt = isset($data['started_at']) ? Carbon::parse($data['started_at']) : now();

        $run = $this->runs()->create([
            'status' => TaskRunStatus::from($data['status'] ?? TaskRunStatus::Success->value),
            'started_at' => $startedAt,
            'finished_at' => $data['finished_at'] ?? now(),
            'duration_ms' => $data['duration_ms'] ?? null,
            'exit_code' => $data['exit_code'] ?? null,
            'output' => $data['output'] ?? null,
            'error' => $data['error'] ?? null,
        ]);

        $this->forceFill([
            'last_run_at' => $startedAt,
            'next_run_at' => $this->calculateNextRun($startedAt),
        ])->save();

        return $run;
    }

    /**
     * Mark a missed run and advance the schedule past the current time.
     */
    public function markMissed(?Carbon $now = null): TaskRun
    {
        $now ??= now();
        $scheduledAt = $this->next_run_at ?? $now;

        $run = $this->runs()->create([
            'status' => TaskRunStatus::Missed,
            'started_at' => $scheduledAt,
            'finished_at' => $scheduledAt,
        ]);

        $this->forceFill([
            'next_run_at' => $this->calculateNextRun($now),
        ])->save();

        return $run;
    }

    /**
     * Determine whether the task has an open (still running) run.
     */
    public function isRunning(): bool
    {
        return $this->runs()->where('status', TaskRunStatus::Running->value)->exists();
    }
}
