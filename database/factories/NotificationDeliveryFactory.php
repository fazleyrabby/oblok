<?php

namespace Database\Factories;

use App\Enums\AlertSeverity;
use App\Enums\DeliveryStatus;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\NotificationChannel;
use App\Models\NotificationDelivery;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alert_event_id' => AlertEvent::factory(),
            'alert_rule_id' => AlertRule::factory(),
            'notification_channel_id' => NotificationChannel::factory(),
            'project_id' => Project::factory(),
            'severity' => AlertSeverity::Warning,
            'subject' => fake()->sentence(),
            'status' => DeliveryStatus::Pending,
            'attempts' => 0,
        ];
    }
}
