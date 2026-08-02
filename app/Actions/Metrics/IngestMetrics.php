<?php

namespace App\Actions\Metrics;

use App\Models\MetricSample;
use App\Models\Project;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class IngestMetrics
{
    /**
     * Persist a batch of metric samples for a project.
     *
     * @param  array<int, array{name: string, value: float|int, labels?: array<string, string|int|float>, recorded_at?: string}>  $samples
     */
    public function handle(Project $project, array $samples): int
    {
        $rows = [];

        foreach ($samples as $sample) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'project_id' => $project->id,
                'name' => (string) $sample['name'],
                'labels' => isset($sample['labels']) ? json_encode($sample['labels']) : null,
                'value' => (float) $sample['value'],
                'recorded_at' => isset($sample['recorded_at'])
                    ? Carbon::parse($sample['recorded_at'])
                    : now(),
            ];
        }

        if ($rows === []) {
            return 0;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            MetricSample::query()->insert($chunk);
        }

        return count($rows);
    }
}
