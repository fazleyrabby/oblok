<?php

namespace App\Jobs;

use App\Actions\Alerts\DispatchAlertRule;
use App\Models\AlertRule;
use App\Support\Alerts\MetricSourceRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EvaluateAlertRulesJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    /**
     * Evaluate all enabled alert rules that are due and dispatch triggered ones.
     */
    public function handle(MetricSourceRegistry $registry, DispatchAlertRule $dispatch): void
    {
        AlertRule::query()
            ->enabled()
            ->dueForEvaluation()
            ->with('channels')
            ->each(function (AlertRule $rule) use ($registry, $dispatch): void {
                $reading = $registry->for($rule->metric)->readingFor($rule);

                if ($reading !== null && $rule->evaluate($reading)) {
                    $dispatch->handle($rule, $reading);
                } else {
                    $rule->update(['last_evaluated_at' => now()]);
                }
            });
    }
}
