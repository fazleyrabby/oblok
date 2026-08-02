<?php

namespace App\Actions\Webhooks;

use App\Actions\Deployments\ProcessDeploymentWebhook;
use App\Models\WebhookCall;
use InvalidArgumentException;

class ReplayWebhook
{
    public function __construct(private ProcessDeploymentWebhook $processDeploymentWebhook) {}

    /**
     * Re-deliver a captured webhook payload through its registered processor.
     */
    public function handle(WebhookCall $webhookCall): WebhookCall
    {
        if ($webhookCall->request_payload === null) {
            throw new InvalidArgumentException('This webhook has no captured payload to replay.');
        }

        if ($webhookCall->event === 'deployment') {
            $this->processDeploymentWebhook->handle($webhookCall->project, $webhookCall->request_payload);
        } else {
            throw new InvalidArgumentException("Webhook event [{$webhookCall->event}] is not replayable.");
        }

        $webhookCall->update([
            'replay_count' => $webhookCall->replay_count + 1,
            'replayed_at' => now(),
        ]);

        return $webhookCall->fresh();
    }
}
