<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Teams\AddProjectMember;
use App\Actions\Teams\RemoveProjectMember;
use App\Actions\Teams\UpdateProjectMemberRole;
use App\Enums\ProjectRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectMemberRequest;
use App\Http\Requests\UpdateProjectMemberRequest;
use App\Http\Resources\ProjectMemberResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectMemberController extends Controller
{
    /**
     * Display a list of project team members.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        return ProjectMemberResource::collection($project->members);
    }

    /**
     * Add a team member to project.
     */
    public function store(StoreProjectMemberRequest $request, Project $project, AddProjectMember $addMember): JsonResponse
    {
        $member = $addMember->handle($project, $request->validated('email'), $request->validated('role'));

        return (new ProjectMemberResource($member))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a team member's role.
     */
    public function update(UpdateProjectMemberRequest $request, Project $project, User $member, UpdateProjectMemberRole $updateRole): ProjectMemberResource
    {
        $updateRole->handle($project, $member, $request->enum('role', ProjectRole::class), $request->user());

        return new ProjectMemberResource($project->members()->where('users.id', $member->id)->first());
    }

    /**
     * Remove a member from project team.
     */
    public function destroy(Project $project, User $member, RemoveProjectMember $removeMember): Response
    {
        $this->authorize('manageMembers', $project);

        $removeMember->handle($project, $member);

        return response()->noContent();
    }
}
