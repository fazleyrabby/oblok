<?php

namespace Database\Factories;

use App\Models\Deployment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Deployment>
 */
class DeploymentFactory extends Factory
{
    protected $model = Deployment::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'environment' => 'production',
            'commit_hash' => Str::random(40),
            'commit_message' => fake()->sentence(),
            'author' => fake()->name(),
            'status' => 'successful',
            'payload' => [
                'provider' => 'github_actions',
                'branch' => 'main',
            ],
            'started_at' => now()->subMinutes(10),
            'finished_at' => now()->subMinutes(8),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
