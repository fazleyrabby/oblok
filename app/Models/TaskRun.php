<?php

namespace App\Models;

use App\Enums\TaskRunStatus;
use Database\Factories\TaskRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $scheduled_task_id
 * @property TaskRunStatus $status
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property int|null $duration_ms
 * @property int|null $exit_code
 * @property string|null $output
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'scheduled_task_id', 'status', 'started_at', 'finished_at', 'duration_ms',
    'exit_code', 'output', 'error',
])]
class TaskRun extends Model
{
    /** @use HasFactory<TaskRunFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskRunStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'duration_ms' => 'integer',
            'exit_code' => 'integer',
        ];
    }

    /**
     * Get the scheduled task this run belongs to.
     *
     * @return BelongsTo<ScheduledTask, $this>
     */
    public function scheduledTask(): BelongsTo
    {
        return $this->belongsTo(ScheduledTask::class);
    }
}
