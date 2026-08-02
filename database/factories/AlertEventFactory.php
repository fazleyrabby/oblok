<?php

namespace Database\Factories;

use App\Enums\AlertSeverity;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertEvent>
 */
class AlertEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alert_rule_id' => AlertRule::factory(),
            'project_id' => Project::factory(),
            'severity' => AlertSeverity::Warning,
            'subject' => fake()->sentence(),
            'triggered_at' => now(),
        ];
    }
}
