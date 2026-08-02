<?php

namespace App\Actions\Queues;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class GetQueueMetricsAction
{
    /**
     * Get real-time queue metrics, worker status, and failed job counts.
     *
     * @return array{
     *     pending_jobs: int,
     *     failed_jobs: int,
     *     horizon_status: string,
     *     recent_failed_jobs: Collection<int, mixed>
     * }
     */
    public function handle(): array
    {
        $pendingJobsCount = DB::table('jobs')->count();
        $failedJobsCount = DB::table('failed_jobs')->count();

        $recentFailedJobs = DB::table('failed_jobs')
            ->latest('failed_at')
            ->take(10)
            ->get();

        $horizonStatus = 'inactive';
        try {
            $masterRepository = app(MasterSupervisorRepository::class);
            $masters = $masterRepository->all();
            if (! empty($masters)) {
                $horizonStatus = 'running';
            }
        } catch (\Throwable) {
            $horizonStatus = 'inactive';
        }

        return [
            'pending_jobs' => $pendingJobsCount,
            'failed_jobs' => $failedJobsCount,
            'horizon_status' => $horizonStatus,
            'recent_failed_jobs' => $recentFailedJobs,
        ];
    }
}
