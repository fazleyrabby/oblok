<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Queues\GetQueueMetricsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class QueueController extends Controller
{
    /**
     * Get queue metrics JSON payload.
     */
    public function metrics(GetQueueMetricsAction $getQueueMetrics): JsonResponse
    {
        $metrics = $getQueueMetrics->handle();

        return response()->json([
            'data' => $metrics,
        ]);
    }
}
