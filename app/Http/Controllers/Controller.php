<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * The projects the authenticated user can access (owner or member).
     *
     * @return Collection<int, Project>
     */
    protected function accessibleProjects(): Collection
    {
        return Project::query()
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhereHas('members', fn ($member) => $member->where('users.id', auth()->id()));
            })
            ->active()
            ->orderBy('name')
            ->get();
    }
}
