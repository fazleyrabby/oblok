<?php

namespace App\Http\Controllers\Web;

use App\Actions\Teams\AddProjectMember;
use App\Actions\Teams\RemoveProjectMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectMemberRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectMemberController extends Controller
{
    /**
     * Display team members management tab for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('view', $project);

        $members = $project->members;

        return view('projects.members', compact('project', 'members'));
    }

    /**
     * Store new team member.
     */
    public function store(StoreProjectMemberRequest $request, Project $project, AddProjectMember $addMember): RedirectResponse
    {
        $addMember->handle($project, $request->validated('email'), $request->validated('role'));

        return redirect()->route('projects.members.index', $project)
            ->with('status', 'Team member added successfully.');
    }

    /**
     * Remove team member.
     */
    public function destroy(Project $project, User $member, RemoveProjectMember $removeMember): RedirectResponse
    {
        $this->authorize('update', $project);

        $removeMember->handle($project, $member);

        return redirect()->route('projects.members.index', $project)
            ->with('status', 'Team member removed.');
    }
}
