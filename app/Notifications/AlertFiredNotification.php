<?php

namespace App\Notifications;

use App\Models\NotificationDelivery;
use App\Notifications\Channels\GenericWebhookChannel;
use App\Notifications\Channels\SlackWebhookChannel;
use App\Support\Notifications\SlackPayloadBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertFiredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public NotificationDelivery $delivery,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return match ($this->delivery->channel->type->value) {
            'slack' => [SlackWebhookChannel::class],
            'webhook' => [GenericWebhookChannel::class],
            default => ['mail'],
        };
    }

    /**
     * Build the mail representation.
     *
     * @return MailMessage
     */
    public function toMail(object $notifiable)
    {
        return (new MailMessage)
            ->subject($this->delivery->subject)
            ->line($this->delivery->subject)
            ->line('Severity: '.$this->delivery->severity->label());
    }

    /**
     * Get the Slack-compatible payload.
     *
     * @return array<string, mixed>
     */
    public function toSlack(object $notifiable): array
    {
        return SlackPayloadBuilder::build($this->delivery);
    }

    /**
     * Get the generic webhook payload.
     *
     * @return array<string, mixed>
     */
    public function toWebhook(object $notifiable): array
    {
        return [
            'event' => 'alert.fired',
            'delivery_id' => $this->delivery->id,
            'project' => [
                'id' => $this->delivery->project_id,
                'name' => $this->delivery->project->name,
            ],
            'rule' => [
                'id' => $this->delivery->alert_rule_id,
                'name' => $this->delivery->alertRule->name,
            ],
            'severity' => $this->delivery->severity->value,
            'subject' => $this->delivery->subject,
            'context' => $this->delivery->payload,
            'occurred_at' => $this->delivery->created_at?->toIso8601String(),
        ];
    }
}
