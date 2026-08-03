<?php

namespace Tests\Feature\Metrics;

use App\Models\MetricSample;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_resource_monitoring_dashboard(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('projects.resources.index', $project));

        $response->assertOk();
        $response->assertSee("Server Resources — {$project->name}");
    }

    public function test_resource_data_endpoint_returns_json_metrics(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        MetricSample::create([
            'project_id' => $project->id,
            'name' => 'system_cpu_usage_percent',
            'value' => 34.5,
            'labels' => ['type' => 'host'],
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('projects.resources.data', [
            'project' => $project,
            'range' => '24h',
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'latest' => ['cpu_percent', 'memory_percent', 'disk_percent'],
            'series',
        ]);
        $this->assertEquals(34.5, $response->json('latest.cpu_percent'));
    }
}
