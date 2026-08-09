<?php

namespace App\Models;

use App\Enums\RunbookRunStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $runbook_id
 * @property string $project_id
 * @property string $triggered_by_type
 * @property string|null $triggered_by_id
 * @property RunbookRunStatus $status
 * @property string|null $output
 * @property int|null $exit_code
 * @property int|null $duration_ms
 * @property Carbon $started_at
 * @property Carbon|null $finished_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'runbook_id', 'project_id', 'triggered_by_type', 'triggered_by_id',
    'status', 'output', 'exit_code', 'duration_ms', 'started_at', 'finished_at',
])]
class RunbookRun extends Model
{
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RunbookRunStatus::class,
            'exit_code' => 'integer',
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * Get the runbook for this execution run.
     *
     * @return BelongsTo<Runbook, $this>
     */
    public function runbook(): BelongsTo
    {
        return $this->belongsTo(Runbook::class);
    }

    /**
     * Get the project for this execution run.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mark the run as running.
     */
    public function markRunning(): bool
    {
        return $this->update([
            'status' => RunbookRunStatus::Running,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the run as successfully finished.
     */
    public function markSuccessful(string $output = '', int $exitCode = 0, int $durationMs = 0): bool
    {
        return $this->update([
            'status' => RunbookRunStatus::Successful,
            'output' => $output,
            'exit_code' => $exitCode,
            'duration_ms' => $durationMs,
            'finished_at' => now(),
        ]);
    }

    /**
     * Mark the run as failed.
     */
    public function markFailed(string $output = '', int $exitCode = 1, int $durationMs = 0): bool
    {
        return $this->update([
            'status' => RunbookRunStatus::Failed,
            'output' => $output,
            'exit_code' => $exitCode,
            'duration_ms' => $durationMs,
            'finished_at' => now(),
        ]);
    }
}
