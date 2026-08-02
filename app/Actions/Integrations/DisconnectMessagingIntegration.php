<?php

namespace App\Actions\Integrations;

use App\Models\MessagingIntegration;

class DisconnectMessagingIntegration
{
    /**
     * Remove a messaging integration and its stored credentials.
     */
    public function handle(MessagingIntegration $integration): void
    {
        $integration->delete();
    }
}
