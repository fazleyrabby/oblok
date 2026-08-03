<?php

namespace Tests\Feature\Metrics;

use App\Models\MetricSample;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_request_analytics_dashboard(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('projects.request-analytics.index', $project));

        $response->assertOk();
        $response->assertSee("Request Analytics — {$project->name}");
    }

    public function test_request_analytics_data_endpoint_returns_json_series(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        MetricSample::create([
            'project_id' => $project->id,
            'name' => 'http_requests_total',
            'value' => 15,
            'labels' => ['method' => 'GET', 'status' => '200'],
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('projects.request-analytics.data', [
            'project' => $project,
            'range' => '24h',
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'total_requests',
            'success_rate',
            'status_counts',
            'method_counts',
            'series',
        ]);
        $this->assertEquals(15, $response->json('total_requests'));
    }
}
