<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ApiKey>
 */
class ApiKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $token = 'atl_'.Str::random(36);

        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'name' => fake()->words(2, true),
            'token' => hash('sha256', $token),
            'key_prefix' => Str::substr($token, 0, 12),
            'requests_count' => 0,
            'last_used_at' => null,
            'expires_at' => null,
            'revoked_at' => null,
        ];
    }
}
