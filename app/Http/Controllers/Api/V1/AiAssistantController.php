<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\AiAssistant\AskAssistant;
use App\Http\Controllers\Controller;
use App\Http\Requests\AskAssistantRequest;
use App\Models\Project;
use App\Services\AiAssistant\Exceptions\AiProviderException;
use Illuminate\Http\JsonResponse;

class AiAssistantController extends Controller
{
    /**
     * Ask the operational assistant about a project.
     */
    public function ask(AskAssistantRequest $request, Project $project, AskAssistant $assistant): JsonResponse
    {
        try {
            $answer = $assistant->handle($project, $request->validated('message'));
        } catch (AiProviderException $e) {
            return response()->json([
                'message' => 'The AI assistant could not be reached.',
                'error' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'data' => [
                'answer' => $answer,
            ],
        ]);
    }
}
