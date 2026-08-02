<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Metrics\IngestMetrics;
use App\Actions\Metrics\QueryMetricSeries;
use App\Http\Controllers\Controller;
use App\Http\Requests\IngestMetricsRequest;
use App\Http\Requests\StoreMetricTargetRequest;
use App\Http\Resources\MetricTargetResource;
use App\Models\MetricSample;
use App\Models\MetricTarget;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class MetricController extends Controller
{
    /**
     * Ingest a batch of metric samples.
     */
    public function store(IngestMetricsRequest $request, Project $project, IngestMetrics $ingest): JsonResponse
    {
        $count = $ingest->handle($project, $request->validated('metrics'));

        return response()->json(['ingested' => $count], 201);
    }

    /**
     * Query down-sampled chart series for a metric.
     */
    public function index(Request $request, Project $project, QueryMetricSeries $query): JsonResponse
    {
        $this->authorize('viewAny', [MetricSample::class, $project]);

        $to = Carbon::parse($request->query('to', now()->toIso8601String()));
        $from = Carbon::parse($request->query('from', now()->subHours(24)->toIso8601String()));

        $series = $query->handle(
            $project,
            (string) $request->query('name'),
            $from,
            $to,
            (int) $request->query('points', 60),
            (string) $request->query('aggregate', 'avg')
        );

        return response()->json(['data' => $series]);
    }

    /**
     * List the project's scrape targets.
     */
    public function targets(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [MetricTarget::class, $project]);

        return MetricTargetResource::collection($project->metricTargets()->latest()->get());
    }

    /**
     * Register a Prometheus-compatible scrape target.
     */
    public function storeTarget(StoreMetricTargetRequest $request, Project $project): MetricTargetResource
    {
        $target = $project->metricTargets()->create($request->validated());

        return new MetricTargetResource($target);
    }

    /**
     * Remove a scrape target.
     */
    public function destroyTarget(Project $project, MetricTarget $metricTarget): JsonResponse
    {
        $this->authorize('delete', $metricTarget);

        $metricTarget->delete();

        return response()->json(['message' => 'Scrape target removed.']);
    }
}
