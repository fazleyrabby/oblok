<?php

namespace App\Actions\Projects;

use App\Models\Project;
use Illuminate\Support\Str;

class UpdateProject
{
    /**
     * Update an existing project.
     *
     * @param  array{name?: string, slug?: string|null, description?: string|null, metadata?: array<string, mixed>|null}  $data
     */
    public function handle(Project $project, array $data): Project
    {
        if (isset($data['slug']) && filled($data['slug']) && $data['slug'] !== $project->slug) {
            $data['slug'] = Str::slug($data['slug']);
        }

        $project->update(array_filter([
            'name' => $data['name'] ?? $project->name,
            'slug' => $data['slug'] ?? $project->slug,
            'description' => array_key_exists('description', $data) ? $data['description'] : $project->description,
            'metadata' => array_key_exists('metadata', $data) ? $data['metadata'] : $project->metadata,
        ], fn ($value) => $value !== null));

        return $project->refresh();
    }
}
