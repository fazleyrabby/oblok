<?php

namespace Database\Factories;

use App\Enums\MessagingPlatform;
use App\Models\MessagingIntegration;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessagingIntegration>
 */
class MessagingIntegrationFactory extends Factory
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
            'platform' => MessagingPlatform::Slack,
            'name' => fake()->company().' workspace',
            'config' => [
                'bot_token' => 'xoxb-'.fake()->sha256(),
                'bot_user_id' => 'U'.fake()->regexify('[A-Z0-9]{9}'),
                'team_id' => 'T'.fake()->regexify('[A-Z0-9]{9}'),
            ],
            'channel' => null,
            'enabled' => true,
            'last_connected_at' => null,
        ];
    }
}
