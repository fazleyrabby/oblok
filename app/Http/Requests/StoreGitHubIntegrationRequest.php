<?php

namespace App\Http\Requests;

use App\Models\GitHubIntegration;
use Illuminate\Foundation\Http\FormRequest;

class StoreGitHubIntegrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project && $this->user()?->can('create', [GitHubIntegration::class, $project]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'repository' => ['required', 'string', 'regex:/^[\w.-]+\/[\w.-]+$/'],
            'access_token' => ['required', 'string', 'min:10'],
        ];
    }

    /**
     * The repository string parsed into owner and name parts.
     *
     * @return array{owner: string, name: string}
     */
    public function repositoryParts(): array
    {
        [$owner, $name] = explode('/', (string) $this->validated('repository'));

        return ['owner' => $owner, 'name' => $name];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'repository.regex' => 'The repository must be in the "owner/name" format.',
        ];
    }
}
