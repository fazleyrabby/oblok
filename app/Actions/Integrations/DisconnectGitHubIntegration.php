<?php

namespace App\Actions\Integrations;

use App\Models\GitHubIntegration;

class DisconnectGitHubIntegration
{
    /**
     * Remove a GitHub integration and its captured commit/PR context.
     */
    public function handle(GitHubIntegration $integration): void
    {
        $integration->delete();
    }
}
