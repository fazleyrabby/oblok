<?php

namespace App\Jobs;

use App\Actions\Services\PingServiceHealth;
use App\Models\Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckServiceHealthJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Service $service,
    ) {}

    public function handle(PingServiceHealth $pingServiceHealth): void
    {
        $pingServiceHealth->handle($this->service);
    }
}
