<?php

namespace App\Listeners;

use App\Actions\Runbooks\TriggerSelfHealingRunbook;
use App\Events\ServiceStatusChanged;

class ExecuteSelfHealingOnServiceFailure
{
    public function __construct(
        protected TriggerSelfHealingRunbook $triggerSelfHealing,
    ) {}

    /**
     * Handle the ServiceStatusChanged event.
     */
    public function handle(ServiceStatusChanged $event): void
    {
        // Ignore flapping services to prevent runaway execution
        if ($event->service->is_flapping) {
            return;
        }

        if ($event->newStatus === 'failing') {
            $service = $event->service;

            if ($service->runbook_id && $service->runbook) {
                $this->triggerSelfHealing->handle(
                    $service->runbook,
                    'service',
                    $service->id
                );
            }
        }
    }
}
