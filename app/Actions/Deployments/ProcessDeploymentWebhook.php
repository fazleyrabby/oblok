<?php

namespace App\Actions\Deployments;

use App\Events\DeploymentStatusChanged;
use App\Models\Deployment;
use App\Models\Project;

class ProcessDeploymentWebhook
{
    /**
     * Process an incoming deployment webhook payload and persist the deployment record.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handle(Project $project, array $payload): Deployment
    {
        $commitHash = $payload['commit_hash'] ?? $payload['head_commit']['id'] ?? $payload['sha'] ?? null;
        $commitMessage = $payload['commit_message'] ?? $payload['head_commit']['message'] ?? $payload['description'] ?? null;
        $author = $payload['author'] ?? $payload['head_commit']['author']['name'] ?? $payload['pusher']['name'] ?? 'Webhook CI';
        $environment = $payload['environment'] ?? 'production';
        $status = $payload['status'] ?? 'successful';

        $deployment = Deployment::create([
            'project_id' => $project->id,
            'environment' => $environment,
            'commit_hash' => $commitHash,
            'commit_message' => $commitMessage,
            'author' => $author,
            'status' => $status,
            'payload' => $payload,
            'started_at' => now(),
            'finished_at' => in_array($status, ['successful', 'failed']) ? now() : null,
        ]);

        DeploymentStatusChanged::dispatch($project, $deployment);

        return $deployment;
    }
}
