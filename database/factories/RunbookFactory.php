<?php

namespace Database\Factories;

use App\Enums\RunbookType;
use App\Models\Project;
use App\Models\Runbook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Runbook>
 */
class RunbookFactory extends Factory
{
    protected $model = Runbook::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => RunbookType::Artisan,
            'config' => ['command' => 'cache:clear'],
            'trigger_type' => 'both',
            'enabled' => true,
            'cooldown_minutes' => 10,
            'timeout_seconds' => 30,
        ];
    }
}
