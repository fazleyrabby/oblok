<?php

namespace App\Actions\Incidents;

use App\Models\Incident;

class ResolveIncident
{
    /**
     * Resolve an active operational incident.
     */
    public function handle(Incident $incident): Incident
    {
        $incident->resolve();

        return $incident->fresh();
    }
}
