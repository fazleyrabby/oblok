<?php

namespace Database\Seeders;

use App\Models\Project;
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

        // Seed demo projects
        Project::factory()->create([
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

        Project::factory()->create([
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
