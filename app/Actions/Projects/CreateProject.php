<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class CreateProject
{
    /**
     * Create a new project for the given user.
     *
     * @param  array{name: string, slug?: string|null, description?: string|null, metadata?: array<string, mixed>|null}  $data
     */
    public function handle(User $user, array $data): Project
    {
        $slug = isset($data['slug']) && filled($data['slug'])
            ? Str::slug($data['slug'])
            : $this->generateUniqueSlug($data['name']);

        return $user->projects()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Generate a unique slug from the project name.
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Project::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
