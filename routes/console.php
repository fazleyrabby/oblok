<?php

use App\Jobs\DispatchScheduledHealthChecksJob;
use App\Jobs\EvaluateAlertRulesJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DispatchScheduledHealthChecksJob)->everyMinute();
Schedule::job(new EvaluateAlertRulesJob)->everyMinute();
