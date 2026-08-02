<?php

namespace App\Jobs;

use App\Models\ScheduledTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckScheduledTasksJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    /**
     * Flag enabled scheduled tasks whose run window has passed without a run.
     */
    public function handle(): void
    {
        ScheduledTask::query()
            ->enabled()
            ->overdue()
            ->each(function (ScheduledTask $task): void {
                $task->markMissed();
            });
    }
}
