<?php

namespace App\Actions\Alerts;

use App\Events\AlertResolved;
use App\Models\AlertEvent;

class ResolveAlertEvent
{
    /**
     * Resolve a firing alert event and clear the owning rule's active pointer.
     */
    public function handle(AlertEvent $event): AlertEvent
    {
        $event->resolve();

        $rule = $event->alertRule;

        if ($rule && $rule->active_event_id === $event->id) {
            $rule->update(['active_event_id' => null]);
        }

        AlertResolved::dispatch($event->project, $event);

        return $event->fresh();
    }
}
