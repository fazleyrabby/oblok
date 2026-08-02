<?php

namespace App\Actions\Teams;

use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddProjectMember
{
    /**
     * Add a user to a project team by email with a specified role.
     */
    public function handle(Project $project, string $email, string $role = 'operator'): User
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'User with this email address was not found.',
            ]);
        }

        if ($user->id === $project->user_id) {
            throw ValidationException::withMessages([
                'email' => 'Project owner is automatically a member.',
            ]);
        }

        $project->members()->syncWithoutDetaching([
            $user->id => ['role' => $role],
        ]);

        return $user;
    }
}
