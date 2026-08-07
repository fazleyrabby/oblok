<?php

namespace App\Events;

use App\Models\AlertEvent;
use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertResolved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Project $project,
        public AlertEvent $alertEvent,
    ) {}

    /**
     * The queue the broadcast job should be dispatched on.
     */
    public string $broadcastQueue = 'broadcasts';

    /**
     * The name of the event on the wire.
     */
    public function broadcastAs(): string
    {
        return 'alert.resolved';
    }

    /**
     * The channel the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('projects.'.$this->project->id)];
    }

    /**
     * The data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'event_id' => $this->alertEvent->id,
            'subject' => $this->alertEvent->subject,
            'severity' => $this->alertEvent->severity,
            'project_id' => $this->project->id,
            'resolved_at' => $this->alertEvent->resolved_at?->toIso8601String(),
        ];
    }
}
