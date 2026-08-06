<?php

namespace App\Listeners;

use App\Actions\Incidents\ResolveIncident;
use App\Events\ServiceStatusChanged;

class ResolveIncidentOnServiceRecovery
{
    public function __construct(
        protected ResolveIncident $resolveIncident,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ServiceStatusChanged $event): void
    {
        if ($event->service->is_flapping) {
            return;
        }

        if ($event->newStatus === 'healthy') {
            $project = $event->service->project;

            $activeIncident = $project->incidents()
                ->open()
                ->first();

            if (! $activeIncident) {
                return;
            }

            // If it is a single-service incident specifically for this service, resolve it.
            if ($activeIncident->service_id === $event->service->id) {
                $this->resolveIncident->handle($activeIncident);

                return;
            }

            // If it is a grouped/project-wide incident, only resolve if no other services are failing.
            if ($activeIncident->service_id === null) {
                $failingServicesCount = $project->services()
                    ->where('status', 'failing')
                    ->count();

                if ($failingServicesCount === 0) {
                    $this->resolveIncident->handle($activeIncident);
                }
            }
        }
    }
}
