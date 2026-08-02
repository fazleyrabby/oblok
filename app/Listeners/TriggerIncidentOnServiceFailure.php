<?php

namespace App\Listeners;

use App\Actions\Incidents\CreateIncident;
use App\Events\ServiceStatusChanged;

class TriggerIncidentOnServiceFailure
{
    public function __construct(
        protected CreateIncident $createIncident,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ServiceStatusChanged $event): void
    {
        if ($event->newStatus === 'failing') {
            $this->createIncident->handle($event->service->project, [
                'service_id' => $event->service->id,
                'title' => "Service Failure Detected: {$event->service->name}",
                'description' => "Monitored target endpoint {$event->service->target} failed health checks.",
                'severity' => 'high',
            ]);
        }
    }
}
