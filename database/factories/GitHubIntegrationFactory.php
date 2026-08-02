<?php

namespace Database\Factories;

use App\Models\GitHubIntegration;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GitHubIntegration>
 */
class GitHubIntegrationFactory extends Factory
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
            'repository_owner' => fake()->userName(),
            'repository_name' => fake()->word(),
            'access_token' => 'ghp_'.fake()->sha256(),
            'default_branch' => 'main',
            'enabled' => true,
            'last_synced_at' => null,
        ];
    }
}
