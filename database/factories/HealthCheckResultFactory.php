<?php

namespace Database\Factories;

use App\Models\HealthCheckResult;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HealthCheckResult>
 */
class HealthCheckResultFactory extends Factory
{
    protected $model = HealthCheckResult::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'status' => 'healthy',
            'status_code' => 200,
            'response_time_ms' => fake()->numberBetween(45, 350),
            'error_message' => null,
            'created_at' => now(),
        ];
    }
}
