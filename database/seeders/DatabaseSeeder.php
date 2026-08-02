<?php

namespace Database\Seeders;

use App\Models\Deployment;
use App\Models\HealthCheckResult;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Atlas Operator',
            'email' => 'admin@atlas.dev',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Seed demo project 1
        $project1 = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Payment Processing Service',
            'slug' => 'payment-processing-service',
            'description' => 'Core stripe payment gateway webhook listener and checkout API service.',
            'metadata' => [
                'environment' => 'production',
                'repository_url' => 'https://github.com/fazleyrabby/payment-service',
                'tech_stack' => ['Laravel', 'PostgreSQL', 'Redis', 'Stripe'],
            ],
            'archived_at' => null,
        ]);

        $service1 = Service::factory()->create([
            'project_id' => $project1->id,
            'name' => 'Stripe Webhook Listener',
            'type' => 'http',
            'target' => 'https://httpbin.org/status/200',
            'check_interval' => 60,
            'status' => 'healthy',
        ]);

        HealthCheckResult::factory()->count(10)->create([
            'service_id' => $service1->id,
            'status' => 'healthy',
            'status_code' => 200,
        ]);

        Deployment::factory()->create([
            'project_id' => $project1->id,
            'environment' => 'production',
            'commit_hash' => 'a40c443b4c8023b8ea1a899cafb4856caa35cafc',
            'commit_message' => 'feat(services): implement phase 4 service health monitoring',
            'author' => 'Fazley Rabbi',
            'status' => 'successful',
        ]);

        // Seed demo project 2
        $project2 = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Authentication & Identity API',
            'slug' => 'authentication-identity-api',
            'description' => 'Central OAuth2 and JWT session authentication provider.',
            'metadata' => [
                'environment' => 'production',
                'repository_url' => 'https://github.com/fazleyrabby/auth-identity-api',
                'tech_stack' => ['Laravel', 'Sanctum', 'Redis'],
            ],
            'archived_at' => null,
        ]);

        $service2 = Service::factory()->create([
            'project_id' => $project2->id,
            'name' => 'OAuth2 Token Endpoint',
            'type' => 'http',
            'target' => 'https://httpbin.org/get',
            'check_interval' => 60,
            'status' => 'healthy',
        ]);

        HealthCheckResult::factory()->count(10)->create([
            'service_id' => $service2->id,
            'status' => 'healthy',
            'status_code' => 200,
        ]);

        Deployment::factory()->create([
            'project_id' => $project2->id,
            'environment' => 'production',
            'commit_hash' => '7abb6a2ff840506ba584f6f3f3b8917cdfd334a8',
            'commit_message' => 'fix(ui): synchronize sidebar collapse state with main layout container',
            'author' => 'Fazley Rabbi',
            'status' => 'successful',
        ]);

        // Seed demo project 3
        Project::factory()->archived()->create([
            'user_id' => $user->id,
            'name' => 'Legacy Mailer Engine v1',
            'slug' => 'legacy-mailer-engine-v1',
            'description' => 'Old SMTP email queue worker service (Decommissioned).',
            'metadata' => [
                'environment' => 'deprecated',
            ],
        ]);
    }
}
