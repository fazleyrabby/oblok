<?php

namespace App\Http\Controllers\Web;

use App\Actions\Metrics\QueryRequestAnalytics;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestAnalyticsController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $projects = Project::query()->orderBy('name')->get();

        return view('request-analytics.index', [
            'project' => $project,
            'projects' => $projects,
        ]);
    }

    public function data(Request $request, Project $project, QueryRequestAnalytics $queryAnalytics): JsonResponse
    {
        $this->authorize('view', $project);

        $range = $request->input('range', '24h');

        $to = now();
        $from = match ($range) {
            '1h' => now()->subHour(),
            '6h' => now()->subHours(6),
            '7d' => now()->subDays(7),
            default => now()->subDay(),
        };

        $data = $queryAnalytics->handle($project, $from, $to);

        return response()->json($data);
    }
}
