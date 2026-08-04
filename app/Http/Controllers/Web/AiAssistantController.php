<?php

namespace App\Http\Controllers\Web;

use App\Actions\AiAssistant\AskAssistant;
use App\Http\Controllers\Controller;
use App\Http\Requests\AskAssistantRequest;
use App\Models\Project;
use App\Services\AiAssistant\Exceptions\AiProviderException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    /**
     * Display the operational AI assistant for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('useAssistant', $project);

        $projects = $this->accessibleProjects();

        return view('ai-assistant.index', compact('projects', 'project'));
    }

    /**
     * Answer a question from the chat panel using the authenticated session.
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
