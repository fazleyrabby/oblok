<?php

namespace App\Jobs;

use App\Actions\Integrations\SyncGitHubData;
use App\Models\GitHubIntegration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncGitHubDataJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 60];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(public string $integrationId) {}

    /**
     * Execute the job.
     */
    public function handle(SyncGitHubData $sync): void
    {
        $integration = GitHubIntegration::find($this->integrationId);

        if (! $integration) {
            return;
        }

        $sync->handle($integration);
    }
}
