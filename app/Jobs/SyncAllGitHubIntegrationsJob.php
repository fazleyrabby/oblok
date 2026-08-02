<?php

namespace App\Jobs;

use App\Models\GitHubIntegration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAllGitHubIntegrationsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Dispatch a sync job for every enabled integration.
     */
    public function handle(): void
    {
        GitHubIntegration::query()
            ->enabled()
            ->pluck('id')
            ->each(fn (string $id) => SyncGitHubDataJob::dispatch($id));
    }
}
