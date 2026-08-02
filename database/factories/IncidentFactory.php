<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'service_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'severity' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => 'investigating',
            'started_at' => now()->subHours(2),
            'resolved_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }
}
