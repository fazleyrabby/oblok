<?php

namespace App\Http\Controllers\Web;

use App\Actions\Queues\GetQueueMetricsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueController extends Controller
{
    /**
     * Display queue operations dashboard.
     */
    public function __invoke(Request $request, GetQueueMetricsAction $getQueueMetrics): View
    {
        $metrics = $getQueueMetrics->handle();

        return view('queues.index', compact('metrics'));
    }
}
