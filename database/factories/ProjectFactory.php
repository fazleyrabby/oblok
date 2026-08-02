<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'user_id' => User::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::random(6),
            'description' => fake()->sentence(),
            'metadata' => [
                'environment' => fake()->randomElement(['production', 'staging', 'development']),
                'repository_url' => fake()->url(),
                'tech_stack' => fake()->randomElements(['Laravel', 'Vue', 'React', 'PostgreSQL', 'Redis'], 3),
            ],
            'archived_at' => null,
        ];
    }

    /**
     * Indicate that the project is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => now(),
        ]);
    }
}
