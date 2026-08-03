<?php

namespace Tests\Feature\Realtime;

use App\Actions\Alerts\DispatchAlertRule;
use App\Actions\Deployments\ProcessDeploymentWebhook;
use App\Actions\Services\PingServiceHealth;
use App\Enums\AlertComparison;
use App\Enums\AlertMetric;
use App\Enums\AlertSeverity;
use App\Events\AlertTriggered;
use App\Events\DeploymentStatusChanged;
use App\Events\ServiceHealthChanged;
use App\Models\AlertRule;
use App\Models\NotificationChannel;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use App\Support\Alerts\MetricReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RealtimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_health_change_broadcasts_on_project_channel(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $service = Service::factory()->create([
            'project_id' => $project->id,
            'target' => 'https://api.example.com/health',
            'expected_status_code' => 200,
            'status' => 'healthy',
        ]);

        Http::fake([
            'https://api.example.com/health' => Http::response([], 500),
        ]);

        app(PingServiceHealth::class)->handle($service);

        Event::assertDispatched(
            ServiceHealthChanged::class,
            fn (ServiceHealthChanged $event) => $event->status === 'failing'
                && $event->service->is($service)
                && $event->broadcastAs() === 'service.health.changed'
                && $event->broadcastOn()[0]->name === 'private-projects.'.$project->id,
        );
    }

    public function test_alert_trigger_broadcasts_on_project_channel(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $channel = NotificationChannel::factory()->create([
            'project_id' => $project->id,
            'type' => 'slack',
        ]);
        $rule = AlertRule::factory()->create([
            'project_id' => $project->id,
            'metric' => AlertMetric::ServiceHealth,
            'comparison' => AlertComparison::Equals,
            'threshold' => 1,
            'severity' => AlertSeverity::Critical,
            'cooldown_minutes' => 10,
        ]);
        $rule->channels()->attach($channel->id);

        app(DispatchAlertRule::class)->handle($rule, new MetricReading(AlertMetric::ServiceHealth, 'failing', now(), []));

        Event::assertDispatched(
            AlertTriggered::class,
            fn (AlertTriggered $event) => $event->alertEvent->subject === $rule->name
                && $event->project->is($project)
                && $event->broadcastAs() === 'alert.triggered',
        );
    }

    public function test_deployment_status_change_broadcasts_on_project_channel(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        app(ProcessDeploymentWebhook::class)->handle($project, [
            'environment' => 'production',
            'commit_hash' => 'abc123',
            'commit_message' => 'ship it',
            'author' => 'ci-bot',
            'status' => 'successful',
        ]);

        Event::assertDispatched(
            DeploymentStatusChanged::class,
            fn (DeploymentStatusChanged $event) => $event->deployment->status === 'successful'
                && $event->project->is($project)
                && $event->broadcastAs() === 'deployment.status.changed',
        );
    }

    public function test_project_channel_only_authorizes_members(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $outsider = User::factory()->create();

        $this->assertTrue($owner->can('view', $project));
        $this->assertFalse($outsider->can('view', $project));
    }

    public function test_health_check_events_include_broadcast_payload(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $service = Service::factory()->create([
            'project_id' => $project->id,
            'target' => 'https://api.example.com/health',
            'status' => 'healthy',
        ]);

        $event = new ServiceHealthChanged($project, $service, 'failing');
        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('service_name', $payload);
        $this->assertArrayHasKey('status', $payload);
        $this->assertArrayHasKey('project_id', $payload);
        $this->assertEquals('failing', $payload['status']);
        $this->assertEquals($service->id, $payload['service_id']);
    }
}
