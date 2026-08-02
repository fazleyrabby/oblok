<?php

namespace Database\Factories;

use App\Models\GitHubIntegration;
use App\Models\GitHubPullRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GitHubPullRequest>
 */
class GitHubPullRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'github_integration_id' => GitHubIntegration::factory(),
            'number' => fake()->unique()->numberBetween(1, 500),
            'title' => fake()->sentence(6),
            'body' => fake()->paragraph(),
            'state' => 'open',
            'author_name' => fake()->name(),
            'opened_at' => fake()->dateTimeThisMonth(),
            'merged_at' => null,
            'closed_at' => null,
            'url' => fake()->url(),
        ];
    }
}
