<?php

namespace Database\Factories;

use App\Models\MetricSample;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetricSample>
 */
class MetricSampleFactory extends Factory
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
            'name' => 'http_requests_total',
            'labels' => [],
            'value' => fake()->randomFloat(2, 0, 100),
            'recorded_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ];
    }
}
