<?php

namespace Database\Factories;

use App\Enums\AlertComparison;
use App\Enums\AlertMetric;
use App\Enums\AlertSeverity;
use App\Models\AlertRule;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertRule>
 */
class AlertRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->words(2, true),
            'metric' => AlertMetric::ServiceHealth,
            'comparison' => AlertComparison::Equals,
            'consecutive_failures' => 1,
            'window_minutes' => 5,
            'severity' => AlertSeverity::Warning,
            'enabled' => true,
            'cooldown_minutes' => 15,
        ];
    }
}
