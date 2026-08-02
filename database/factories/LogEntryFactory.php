<?php

namespace Database\Factories;

use App\Models\LogEntry;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogEntry>
 */
class LogEntryFactory extends Factory
{
    protected $model = LogEntry::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'level' => fake()->randomElement(['debug', 'info', 'warning', 'error', 'critical']),
            'message' => fake()->sentence(),
            'context' => [
                'user_id' => fake()->uuid(),
                'ip' => fake()->ipv4(),
                'route' => '/api/v1/checkout',
            ],
            'channel' => 'production',
            'created_at' => now(),
        ];
    }

    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'error',
        ]);
    }
}
