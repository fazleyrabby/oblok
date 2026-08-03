<?php

namespace App\Actions\Alerts;

use App\Enums\DeliveryStatus;
use App\Enums\NotificationChannelType;
use App\Enums\ProjectRole;
use App\Events\AlertTriggered;
use App\Jobs\DeliverNotification;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\NotificationChannel;
use App\Models\NotificationDelivery;
use App\Models\Project;
use App\Models\User;
use App\Support\Alerts\MetricReading;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class DispatchAlertRule
{
    /**
     * Create an alert event for a triggered rule and fan out deliveries.
     */
    public function handle(AlertRule $rule, MetricReading $reading): ?AlertEvent
    {
        if ($rule->isInCooldown()) {
            return null;
        }

        return DB::transaction(function () use ($rule, $reading) {
            $event = AlertEvent::create([
                'alert_rule_id' => $rule->id,
                'project_id' => $rule->project_id,
                'severity' => $rule->severity,
                'subject' => $rule->name,
                'context' => $reading->context,
                'triggered_at' => now(),
            ]);

            $rule->update([
                'last_triggered_at' => now(),
                'last_evaluated_at' => now(),
            ]);

            AlertTriggered::dispatch($rule->project, $event);

            foreach ($rule->channels as $channel) {
                if ($channel->type === NotificationChannelType::Mail) {
                    $this->dispatchMailDeliveries($rule, $event, $channel, $reading);
                } else {
                    $this->dispatchChannelDelivery($rule, $event, $channel, $reading);
                }
            }

            return $event;
        });
    }

    /**
     * Create and dispatch a delivery per recipient for a mail channel.
     */
    protected function dispatchMailDeliveries(AlertRule $rule, AlertEvent $event, NotificationChannel $channel, MetricReading $reading): void
    {
        $recipients = $this->mailRecipients($rule->project, $channel->pivot->recipient_filter);

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($recipients as $recipient) {
            $delivery = $this->createDelivery($rule, $event, $channel, $reading);

            DeliverNotification::dispatch($delivery, $recipient)->onQueue('notifications');
        }
    }

    /**
     * Create and dispatch a single delivery for a Slack or webhook channel.
     */
    protected function dispatchChannelDelivery(AlertRule $rule, AlertEvent $event, NotificationChannel $channel, MetricReading $reading): void
    {
        $delivery = $this->createDelivery($rule, $event, $channel, $reading);

        DeliverNotification::dispatch($delivery)->onQueue('notifications');
    }

    /**
     * Create a pending delivery record for an alert event.
     */
    protected function createDelivery(AlertRule $rule, AlertEvent $event, NotificationChannel $channel, MetricReading $reading): NotificationDelivery
    {
        return NotificationDelivery::create([
            'alert_event_id' => $event->id,
            'alert_rule_id' => $rule->id,
            'notification_channel_id' => $channel->id,
            'project_id' => $rule->project_id,
            'severity' => $rule->severity,
            'subject' => $rule->name,
            'payload' => $reading->context,
            'status' => DeliveryStatus::Pending,
        ]);
    }

    /**
     * Resolve the users to notify through a mail channel.
     *
     * @return EloquentCollection<int, User>
     */
    protected function mailRecipients(Project $project, mixed $recipientFilter): EloquentCollection
    {
        $recipientFilter = is_string($recipientFilter) ? json_decode($recipientFilter, true) : $recipientFilter;

        $roles = $recipientFilter['roles'] ?? [ProjectRole::Admin->value, ProjectRole::Operator->value];

        return $project->members()
            ->whereIn('project_members.role', $roles)
            ->get();
    }
}
