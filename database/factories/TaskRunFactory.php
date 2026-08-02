<?php

namespace Database\Factories;

use App\Enums\TaskRunStatus;
use App\Models\ScheduledTask;
use App\Models\TaskRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskRun>
 */
class TaskRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subMinutes(10);

        return [
            'scheduled_task_id' => ScheduledTask::factory(),
            'status' => TaskRunStatus::Success,
            'started_at' => $startedAt,
            'finished_at' => $startedAt->copy()->addSeconds(3),
            'duration_ms' => 3000,
            'exit_code' => 0,
            'output' => 'Task completed successfully.',
        ];
    }
}
