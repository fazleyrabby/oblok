<?php

namespace App\Http\Controllers\Web;

use App\Actions\ApiKeys\CreateApiKey;
use App\Actions\ApiKeys\RevokeApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiKeyRequest;
use App\Models\ApiKey;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    /**
     * Display the project's API keys.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [ApiKey::class, $project]);

        $keys = $project->apiKeys()->latest()->get();

        $projects = $this->accessibleProjects();

        return view('api-keys.index', compact('projects', 'project', 'keys'));
    }

    /**
     * Create a new API key for the project.
     */
    public function store(
        StoreApiKeyRequest $request,
        Project $project,
        CreateApiKey $create
    ): RedirectResponse {
        $result = $create->handle(
            $request->user(),
            $project,
            $request->validated('name'),
            $request->date('expires_at')
        );

        return redirect()->route('projects.api-keys.index', $project)
            ->with('createdApiKey', $result['token'])
            ->with('createdApiKeyName', $result['key']->name)
            ->with('status', 'API key created. Copy it now — it will not be shown again.');
    }

    /**
     * Revoke an API key.
     */
    public function destroy(
        Project $project,
        ApiKey $apiKey,
        RevokeApiKey $revoke
    ): RedirectResponse {
        $this->authorize('delete', $apiKey);

        $revoke->handle($apiKey);

        return redirect()->route('projects.api-keys.index', $project)
            ->with('status', 'API key revoked.');
    }
}
