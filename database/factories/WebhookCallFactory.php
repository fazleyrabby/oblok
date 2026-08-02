<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\WebhookCall;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookCall>
 */
class WebhookCallFactory extends Factory
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
            'event' => 'deployment',
            'method' => 'POST',
            'url' => 'api/v1/webhooks/deployments/demo-project',
            'status_code' => 201,
            'request_headers' => [
                'content-type' => ['application/json'],
                'user-agent' => ['Stripe/1.0'],
            ],
            'request_payload' => [
                'environment' => 'production',
                'commit_hash' => 'abc123',
                'status' => 'successful',
            ],
            'response_payload' => ['deployment_id' => '00000000-0000-0000-0000-000000000000'],
            'processing_time_ms' => 42,
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Stripe/1.0',
            'replay_count' => 0,
        ];
    }
}
