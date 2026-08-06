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
        if ($event->service->is_flapping) {
            return;
        }

        if ($event->newStatus === 'failing') {
            $project = $event->service->project;

            $activeIncident = $project->incidents()
                ->open()
                ->first();

            if ($activeIncident) {
                // Deduplicate: If it is already associated with this service, do nothing
                if ($activeIncident->service_id === $event->service->id) {
                    return;
                }

                // Group: Append detail to existing incident
                $timestamp = now()->toDateTimeString();
                $failureDetail = "\n- Service '{$event->service->name}' failed at {$timestamp}";
                $newDescription = ($activeIncident->description ?? '').$failureDetail;

                $activeIncident->update([
                    'title' => 'Multiple Service Failures Detected',
                    'description' => trim($newDescription),
                    'service_id' => null, // Multi-service/project-wide
                ]);
            } else {
                $this->createIncident->handle($project, [
                    'service_id' => $event->service->id,
                    'title' => "Service Failure Detected: {$event->service->name}",
                    'description' => "Monitored target endpoint {$event->service->target} failed health checks.",
                    'severity' => 'high',
                ]);
            }
        }
    }
}
