<?php

namespace App\Jobs;

use App\Models\Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchScheduledHealthChecksJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Service::query()
            ->whereNull('last_checked_at')
            ->orWhere('last_checked_at', '<=', now()->subMinutes(1))
            ->chunkById(100, function ($services) {
                foreach ($services as $service) {
                    CheckServiceHealthJob::dispatch($service);
                }
            });
    }
}
