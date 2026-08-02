<?php

namespace App\Jobs;

use App\Enums\NotificationChannelType;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Notifications\AlertFiredNotification;
use App\Notifications\Channels\GenericWebhookChannel;
use App\Notifications\Channels\SlackWebhookChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class DeliverNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300, 900, 3600];

    public int $timeout = 30;

    public function __construct(
        public NotificationDelivery $delivery,
        public ?User $recipient = null,
    ) {}

    /**
     * Deliver the notification through the configured channel.
     */
    public function handle(): void
    {
        try {
            $this->delivery->increment('attempts');

            $notification = new AlertFiredNotification($this->delivery);

            if ($this->delivery->channel->type === NotificationChannelType::Mail) {
                Notification::send($this->recipient ?? $this->delivery->project->user, $notification);
            } else {
                $channel = $this->delivery->channel->type === NotificationChannelType::Slack
                    ? app(SlackWebhookChannel::class)
                    : app(GenericWebhookChannel::class);

                $channel->send(new AnonymousNotifiable, $notification);
            }

            $this->delivery->markDelivered();
        } catch (\Throwable $e) {
            $this->delivery->markFailed($e->getMessage());

            throw $e;
        }
    }
}
