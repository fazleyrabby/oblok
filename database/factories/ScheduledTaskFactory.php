<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ScheduledTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduledTask>
 */
class ScheduledTaskFactory extends Factory
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
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'command' => fake()->randomElement(['php artisan schedule:run', 'php artisan backup:run', 'php artisan metrics:rollup']),
            'cron_expression' => '*/5 * * * *',
            'timezone' => 'UTC',
            'enabled' => true,
            'next_run_at' => now()->addMinutes(5),
        ];
    }
}
