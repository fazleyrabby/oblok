<?php

namespace App\Notifications\Channels;

use App\Notifications\AlertFiredNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class GenericWebhookChannel
{
    /**
     * Send the given notification to a generic webhook URL with HMAC signature.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebhook')) {
            return;
        }

        $payload = $notification->toWebhook($notifiable);

        /** @var AlertFiredNotification $notification */
        $config = $notification->delivery->channel->encrypted_config ?? [];

        $url = $config['url'] ?? null;

        if (! $url) {
            return;
        }

        $request = Http::timeout(10);

        $secret = $config['secret'] ?? null;

        if ($secret) {
            $signature = hash_hmac('sha256', json_encode($payload), $secret);
            $request = $request->withHeaders(['X-Atlas-Signature' => $signature]);
        }

        $request->post($url, $payload);
    }
}
