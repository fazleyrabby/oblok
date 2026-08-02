<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->domainWord().' API',
            'type' => 'http',
            'target' => 'https://'.fake()->domainName(),
            'check_interval' => 60,
            'timeout' => 5,
            'expected_status_code' => 200,
            'status' => 'healthy',
            'last_checked_at' => now(),
        ];
    }

    public function failing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failing',
        ]);
    }
}
