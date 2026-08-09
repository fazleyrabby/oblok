<?php

namespace App\Listeners;

use App\Actions\Runbooks\TriggerSelfHealingRunbook;
use App\Events\AlertTriggered;

class ExecuteSelfHealingOnAlertTriggered
{
    public function __construct(
        protected TriggerSelfHealingRunbook $triggerSelfHealing,
    ) {}

    /**
     * Handle the AlertTriggered event.
     */
    public function handle(AlertTriggered $event): void
    {
        $alertRule = $event->alertEvent->alertRule;

        if ($alertRule && $alertRule->runbook_id && $alertRule->runbook) {
            $this->triggerSelfHealing->handle(
                $alertRule->runbook,
                'alert_rule',
                $alertRule->id
            );
        }
    }
}
