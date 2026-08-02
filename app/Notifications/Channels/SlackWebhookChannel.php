<?php

namespace App\Notifications\Channels;

use App\Notifications\AlertFiredNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class SlackWebhookChannel
{
    /**
     * Send the given notification to a Slack incoming webhook URL.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSlack')) {
            return;
        }

        $payload = $notification->toSlack($notifiable);

        /** @var AlertFiredNotification $notification */
        $config = $notification->delivery->channel->encrypted_config ?? [];

        $webhookUrl = $config['webhook_url'] ?? null;

        if (! $webhookUrl) {
            return;
        }

        Http::timeout(10)->post($webhookUrl, $payload);
    }
}
