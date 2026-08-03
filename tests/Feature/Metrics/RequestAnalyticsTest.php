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

    public function test_request_analytics_series_is_downsampled_to_avoid_freeze(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        // Simulate a high-frequency counter recorded every 2s for the last hour
        // (old code bucketed per-second and produced ~3600 points/series -> freeze).
        $start = now()->subHour();
        for ($i = 0; $i < 1800; $i++) {
            MetricSample::create([
                'project_id' => $project->id,
                'name' => 'http_requests_total',
                'value' => 1,
                'labels' => ['method' => 'GET', 'status' => '200'],
                'recorded_at' => $start->copy()->addSeconds($i * 2),
            ]);
        }

        $response = $this->actingAs($user)->get(route('projects.request-analytics.data', [
            'project' => $project,
            'range' => '24h',
        ]));

        $response->assertOk();

        $allX = [];
        foreach ($response->json('series') as $s) {
            foreach ($s['data'] as $point) {
                $allX[] = $point['x'];
            }
        }

        // Time buckets must be capped regardless of raw sample volume.
        $this->assertLessThanOrEqual(120, count(array_unique($allX)), 'Time buckets must be capped');
    }
}
