<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ApiKeys\CreateApiKey;
use App\Actions\ApiKeys\RevokeApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiKeyRequest;
use App\Http\Resources\ApiKeyResource;
use App\Models\ApiKey;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApiKeyController extends Controller
{
    /**
     * List the project's API keys.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [ApiKey::class, $project]);

        return ApiKeyResource::collection($project->apiKeys()->latest()->paginate());
    }

    /**
     * Create a new API key for the project.
     */
    public function store(
        StoreApiKeyRequest $request,
        Project $project,
        CreateApiKey $create
    ): JsonResponse {
        $result = $create->handle(
            $request->user(),
            $project,
            $request->validated('name'),
            $request->date('expires_at')
        );

        return response()->json([
            'data' => (new ApiKeyResource($result['key']))->resolve(),
            'token' => $result['token'],
        ], 201);
    }

    /**
     * Revoke an API key.
     */
    public function destroy(Project $project, ApiKey $apiKey, RevokeApiKey $revoke): JsonResponse
    {
        $this->authorize('delete', $apiKey);

        $revoke->handle($apiKey);

        return response()->json(['message' => 'API key revoked.']);
    }
}
