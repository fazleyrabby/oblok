<?php

namespace App\Events;

use App\Models\Deployment;
use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeploymentStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Project $project,
        public Deployment $deployment,
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
        return 'deployment.status.changed';
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
            'deployment_id' => $this->deployment->id,
            'environment' => $this->deployment->environment,
            'status' => $this->deployment->status,
            'commit_hash' => $this->deployment->commit_hash,
            'commit_message' => $this->deployment->commit_message,
            'project_id' => $this->project->id,
            'at' => $this->deployment->started_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
