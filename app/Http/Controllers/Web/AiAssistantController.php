<?php

namespace App\Http\Controllers\Web;

use App\Actions\AiAssistant\AskAssistant;
use App\Http\Controllers\Controller;
use App\Http\Requests\AskAssistantRequest;
use App\Models\Project;
use App\Services\AiAssistant\Exceptions\AiProviderException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AiAssistantController extends Controller
{
    /**
     * Display the operational AI assistant for a project, preloaded with the
     * user's persistent chat history.
     */
    public function index(Project $project): View
    {
        $this->authorize('useAssistant', $project);

        $projects = $this->accessibleProjects();
        $conversation = $project->conversations()->forUser(auth()->user())->first();
        $messages = $conversation?->messages()->get(['role', 'content']) ?? collect();

        $customProviders = $project->aiProviders()->get();
        $availableModels = collect();

        $defaultModel = config('oblok.ai.model');
        if ($defaultModel) {
            $availableModels->push([
                'provider_id' => '',
                'model' => $defaultModel,
                'label' => $defaultModel . ' (Default)',
            ]);
        }

        foreach ($customProviders as $prov) {
            foreach ($prov->models as $modelName) {
                $availableModels->push([
                    'provider_id' => $prov->id,
                    'model' => $modelName,
                    'label' => $modelName . ' (' . $prov->name . ')',
                ]);
            }
        }

        $selectedProviderId = $conversation?->selected_provider_id;
        $selectedModel = $conversation?->selected_model ?? $defaultModel;

        return view('ai-assistant.index', compact(
            'projects',
            'project',
            'messages',
            'availableModels',
            'selectedProviderId',
            'selectedModel'
        ));
    }

    /**
     * Select/update the active AI model and provider for the conversation.
     */
    public function selectModel(Request $request, Project $project): JsonResponse
    {
        $this->authorize('useAssistant', $project);

        $validated = $request->validate([
            'provider_id' => 'nullable|uuid|exists:ai_providers,id',
            'model' => 'nullable|string|max:255',
        ]);

        $conversation = $project->conversations()->firstOrCreate(
            ['user_id' => auth()->id()],
            ['title' => null]
        );

        $conversation->update([
            'selected_provider_id' => $validated['provider_id'] ?: null,
            'selected_model' => $validated['model'] ?: null,
        ]);

        return response()->json(['data' => ['success' => true]]);
    }

    /**
     * Answer a question from the chat panel, streaming tokens to the client as
     * Server-Sent Events and persisting the exchange to chat history.
     */
    public function ask(AskAssistantRequest $request, Project $project, AskAssistant $assistant): StreamedResponse
    {
        $question = (string) $request->validated('message');

        return new StreamedResponse(function () use ($assistant, $project, $request, $question) {
            try {
                foreach ($assistant->stream($project, $request->user(), $question) as $chunk) {
                    $this->emit('token', ['answer' => $chunk]);
                }

                $this->emit('done', ['status' => 'ok']);
            } catch (AiProviderException $e) {
                $this->emit('error', [
                    'message' => 'The AI assistant could not be reached.',
                    'error' => $e->getMessage(),
                ]);
            } catch (Throwable $e) {
                $this->emit('error', [
                    'message' => 'The AI assistant could not be reached.',
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Delete the persisted chat history for the project and user.
     */
    public function clear(Request $request, Project $project, AskAssistant $assistant): JsonResponse
    {
        $this->authorize('useAssistant', $project);

        $assistant->clear($project, $request->user());

        return response()->json(['data' => ['cleared' => true]]);
    }

    /**
     * Write a single Server-Sent Event to the response stream.
     *
     * @param  array<string, mixed>  $data
     */
    protected function emit(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: '.json_encode($data)."\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
