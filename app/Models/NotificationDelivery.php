<?php

namespace App\Models;

use App\Enums\AlertSeverity;
use App\Enums\DeliveryStatus;
use Database\Factories\NotificationDeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $alert_event_id
 * @property string $alert_rule_id
 * @property string $notification_channel_id
 * @property string $project_id
 * @property AlertSeverity $severity
 * @property string $subject
 * @property array<string, mixed>|null $payload
 * @property DeliveryStatus $status
 * @property int $attempts
 * @property string|null $last_error
 * @property Carbon|null $delivered_at
 * @property Carbon|null $acknowledged_at
 * @property string|null $acknowledged_by
 * @property Carbon|null $snoozed_until
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'alert_event_id', 'alert_rule_id', 'notification_channel_id', 'project_id',
    'severity', 'subject', 'payload', 'status', 'attempts', 'last_error',
    'delivered_at', 'acknowledged_at', 'acknowledged_by', 'snoozed_until',
])]
class NotificationDelivery extends Model
{
    /** @use HasFactory<NotificationDeliveryFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity' => AlertSeverity::class,
            'payload' => 'array',
            'status' => DeliveryStatus::class,
            'attempts' => 'integer',
            'delivered_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'snoozed_until' => 'datetime',
        ];
    }

    /**
     * Get the alert event that produced this delivery.
     *
     * @return BelongsTo<AlertEvent, $this>
     */
    public function alertEvent(): BelongsTo
    {
        return $this->belongsTo(AlertEvent::class);
    }

    /**
     * Get the alert rule this delivery belongs to.
     *
     * @return BelongsTo<AlertRule, $this>
     */
    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class);
    }

    /**
     * Get the notification channel used for this delivery.
     *
     * @return BelongsTo<NotificationChannel, $this>
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'notification_channel_id');
    }

    /**
     * Get the project this delivery belongs to.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who acknowledged this delivery.
     *
     * @return BelongsTo<User, $this>
     */
    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Mark the delivery as delivered.
     */
    public function markDelivered(): bool
    {
        return $this->update([
            'status' => DeliveryStatus::Delivered,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark the delivery as failed with an error message.
     */
    public function markFailed(string $error): bool
    {
        return $this->update([
            'status' => DeliveryStatus::Failed,
            'last_error' => $error,
        ]);
    }

    /**
     * Acknowledge the delivery.
     */
    public function acknowledge(User $user): bool
    {
        return $this->update([
            'status' => DeliveryStatus::Acknowledged,
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
            'snoozed_until' => null,
        ]);
    }

    /**
     * Snooze the delivery until a given time.
     */
    public function snooze(Carbon $until): bool
    {
        return $this->update([
            'status' => DeliveryStatus::Snoozed,
            'snoozed_until' => $until,
        ]);
    }
}
