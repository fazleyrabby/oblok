<?php

namespace App\Actions\Runbooks;

use App\Jobs\ExecuteRunbookJob;
use App\Models\Runbook;

class TriggerSelfHealingRunbook
{
    /**
     * Evaluate and dispatch a self-healing runbook execution if eligible.
     */
    public function handle(?Runbook $runbook, string $triggeredByType, ?string $triggeredById = null): bool
    {
        if (! $runbook || ! $runbook->enabled) {
            return false;
        }

        if (! in_array($runbook->trigger_type, ['automatic', 'both'], true)) {
            return false;
        }

        if ($runbook->isInCooldown()) {
            return false;
        }

        ExecuteRunbookJob::dispatch($runbook, $triggeredByType, $triggeredById);

        return true;
    }
}
