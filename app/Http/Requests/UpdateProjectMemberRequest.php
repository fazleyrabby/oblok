<?php

namespace App\Http\Requests;

use App\Enums\ProjectRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\In;

class UpdateProjectMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project && $this->user()?->can('manageMembers', $project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $assignable = array_map(
            fn (ProjectRole $role) => $role->value,
            [ProjectRole::Admin, ProjectRole::Operator, ProjectRole::Viewer],
        );

        return [
            'role' => ['required', 'string', new In($assignable)],
        ];
    }
}
