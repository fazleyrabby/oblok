<?php

namespace App\Jobs;

use App\Actions\Alerts\DispatchAlertRule;
use App\Actions\Alerts\ResolveAlertEvent;
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
     *
     * When a rule's condition is no longer met but the rule has an active firing
     * event, the event is automatically resolved.
     */
    public function handle(MetricSourceRegistry $registry, DispatchAlertRule $dispatch, ResolveAlertEvent $resolve): void
    {
        AlertRule::query()
            ->enabled()
            ->dueForEvaluation()
            ->with('channels')
            ->each(function (AlertRule $rule) use ($registry, $dispatch, $resolve): void {
                $reading = $registry->for($rule->metric)->readingFor($rule);

                if ($reading !== null && $rule->evaluate($reading)) {
                    $dispatch->handle($rule, $reading);
                } else {
                    // Condition no longer met: resolve active alert if one exists.
                    if ($rule->isFiring()) {
                        $activeEvent = $rule->activeEvent;

                        if ($activeEvent && $activeEvent->isFiring()) {
                            $resolve->handle($activeEvent);
                        } else {
                            // Stale pointer — clear it.
                            $rule->update(['active_event_id' => null]);
                        }
                    }

                    $rule->update(['last_evaluated_at' => now()]);
                }
            });
    }
}

