<?php

namespace App\Jobs;

use App\Actions\Runbooks\ExecuteRunbook;
use App\Models\Runbook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExecuteRunbookJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public function __construct(
        public Runbook $runbook,
        public string $triggeredByType = 'manual',
        public ?string $triggeredById = null,
    ) {}

    /**
     * Execute the queued runbook job.
     */
    public function handle(ExecuteRunbook $executeRunbook): void
    {
        $executeRunbook->handle($this->runbook, $this->triggeredByType, $this->triggeredById);
    }
}
