<?php

namespace App\Http\Controllers\Web;

use App\Actions\Integrations\ConnectMessagingIntegration;
use App\Actions\Integrations\DisconnectMessagingIntegration;
use App\Actions\Integrations\SendMessagingMessage;
use App\Enums\MessagingPlatform;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessagingMessageRequest;
use App\Http\Requests\StoreMessagingIntegrationRequest;
use App\Models\MessagingIntegration;
use App\Models\Project;
use App\Services\Messaging\Exceptions\MessagingApiException;
use App\Services\Messaging\MessagingDriverRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MessagingIntegrationController extends Controller
{
    /**
     * Display the messaging integration overview for a project.
     */
    public function index(Project $project, MessagingDriverRegistry $drivers): View
    {
        $this->authorize('viewAny', [MessagingIntegration::class, $project]);

        $integration = $project->messagingIntegration;

        $channels = new Collection;
        $channelError = null;

        if ($integration) {
            try {
                $channels = collect($drivers->for($integration->platform)->channels($integration->config));
            } catch (MessagingApiException $e) {
                $channelError = $e->getMessage();
            }
        }

        $projects = $this->accessibleProjects();

        return view('messaging.index', compact('projects', 'project', 'integration', 'channels', 'channelError'));
    }

    /**
     * Connect a chat platform to the project.
     */
    public function store(
        StoreMessagingIntegrationRequest $request,
        Project $project,
        ConnectMessagingIntegration $connect
    ): RedirectResponse {
        $platform = MessagingPlatform::from($request->validated('platform'));

        try {
            $integration = $connect->handle(
                $project,
                $platform,
                ['bot_token' => $request->validated('bot_token')],
                $request->validated('channel')
            );
        } catch (MessagingApiException $e) {
            return back()->withErrors(['bot_token' => $e->getMessage()])->withInput();
        }

        return redirect()->route('projects.messaging.index', $project)
            ->with('status', "Connected to {$integration->name} ({$integration->platform->label()}).");
    }

    /**
     * Send a message through the connected integration.
     */
    public function send(
        SendMessagingMessageRequest $request,
        Project $project,
        MessagingIntegration $integration,
        SendMessagingMessage $send
    ): RedirectResponse {
        try {
            $send->handle($integration, $request->validated('channel'), $request->validated('message'));
        } catch (MessagingApiException $e) {
            return back()->withErrors(['message' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Message sent successfully.');
    }

    /**
     * Disconnect the messaging integration from the project.
     */
    public function destroy(
        Project $project,
        MessagingIntegration $integration,
        DisconnectMessagingIntegration $disconnect
    ): RedirectResponse {
        $this->authorize('delete', $integration);

        $disconnect->handle($integration);

        return redirect()->route('projects.messaging.index', $project)
            ->with('status', 'Messaging integration disconnected.');
    }
}
