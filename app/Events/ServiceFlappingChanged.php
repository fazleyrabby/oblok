<?php

namespace App\Events;

use App\Models\Project;
use App\Models\Service;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceFlappingChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Project $project,
        public Service $service,
        public bool $isFlapping,
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
        return 'service.flapping.changed';
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
            'service_id' => $this->service->id,
            'service_name' => $this->service->name,
            'is_flapping' => $this->isFlapping,
            'status' => $this->service->status,
            'project_id' => $this->project->id,
            'at' => now()->toIso8601String(),
        ];
    }
}
