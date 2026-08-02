<?php

namespace App\Http\Controllers\Web;

use App\Actions\Metrics\QueryMetricSeries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMetricTargetRequest;
use App\Models\MetricSample;
use App\Models\MetricTarget;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MetricController extends Controller
{
    /**
     * Display the metrics dashboard for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [MetricSample::class, $project]);

        $names = $project->metricSamples()->distinct()->orderBy('name')->pluck('name');
        $targets = $project->metricTargets()->latest()->get();

        return view('metrics.index', compact('project', 'names', 'targets'));
    }

    /**
     * Return down-sampled chart series for a metric.
     */
    public function data(Request $request, Project $project, QueryMetricSeries $query): JsonResponse
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

        return response()->json([
            'name' => (string) $request->query('name'),
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'series' => $series,
        ]);
    }

    /**
     * Register a Prometheus-compatible scrape target.
     */
    public function storeTarget(StoreMetricTargetRequest $request, Project $project): RedirectResponse
    {
        $project->metricTargets()->create($request->validated());

        return back()->with('status', 'Scrape target added. It will be collected every minute.');
    }

    /**
     * Remove a scrape target.
     */
    public function destroyTarget(Project $project, MetricTarget $metricTarget): RedirectResponse
    {
        $this->authorize('delete', $metricTarget);

        $metricTarget->delete();

        return back()->with('status', 'Scrape target removed.');
    }
}
