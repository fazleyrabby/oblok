<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationChannelRequest;
use App\Http\Requests\UpdateNotificationChannelRequest;
use App\Models\NotificationChannel;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationChannelController extends Controller
{
    /**
     * Display a listing of notification channels for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [NotificationChannel::class, $project]);

        $channels = $project->notificationChannels;

        $projects = $this->accessibleProjects();

        return view('notification-channels.index', compact('projects', 'project', 'channels'));
    }

    /**
     * Show the form for creating a new notification channel.
     */
    public function create(Project $project): View
    {
        $this->authorize('create', [NotificationChannel::class, $project]);

        return view('notification-channels.create', compact('project'));
    }

    /**
     * Store a newly created notification channel.
     */
    public function store(StoreNotificationChannelRequest $request, Project $project): RedirectResponse
    {
        $project->notificationChannels()->create($this->normalizeConfig($request->validated()));

        return redirect()->route('projects.notification-channels.index', $project)
            ->with('status', 'Notification channel created successfully.');
    }

    /**
     * Show the form for editing a notification channel.
     */
    public function edit(Project $project, NotificationChannel $notificationChannel): View
    {
        $this->authorize('update', $notificationChannel);

        return view('notification-channels.edit', compact('project', 'notificationChannel'));
    }

    /**
     * Update the notification channel.
     */
    public function update(UpdateNotificationChannelRequest $request, Project $project, NotificationChannel $notificationChannel): RedirectResponse
    {
        $notificationChannel->update($this->normalizeConfig($request->validated()));

        return redirect()->route('projects.notification-channels.index', $project)
            ->with('status', 'Notification channel updated successfully.');
    }

    /**
     * Map the flat "config" field to the encrypted_config column.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeConfig(array $data): array
    {
        if (isset($data['config'])) {
            $data['encrypted_config'] = $data['config'];
            unset($data['config']);
        }

        return $data;
    }

    /**
     * Delete the notification channel.
     */
    public function destroy(Project $project, NotificationChannel $notificationChannel): RedirectResponse
    {
        $this->authorize('delete', $notificationChannel);

        $notificationChannel->delete();

        return redirect()->route('projects.notification-channels.index', $project)
            ->with('status', 'Notification channel deleted.');
    }
}
