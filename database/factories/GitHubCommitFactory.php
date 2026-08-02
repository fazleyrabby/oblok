<?php

namespace Database\Factories;

use App\Models\GitHubCommit;
use App\Models\GitHubIntegration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GitHubCommit>
 */
class GitHubCommitFactory extends Factory
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
            'sha' => fake()->sha1(),
            'message' => fake()->sentence(),
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'authored_at' => fake()->dateTimeThisMonth(),
            'url' => fake()->url(),
        ];
    }
}
