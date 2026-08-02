<?php

namespace App\Jobs;

use App\Actions\Integrations\SendMessagingMessage;
use App\Models\MessagingIntegration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMessagingMessageJob implements ShouldQueue
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
    public function __construct(
        public string $integrationId,
        public string $channel,
        public string $message,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SendMessagingMessage $send): void
    {
        $integration = MessagingIntegration::find($this->integrationId);

        if (! $integration) {
            return;
        }

        $send->handle($integration, $this->channel, $this->message);
    }
}
