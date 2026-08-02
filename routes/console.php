<?php

use App\Jobs\CheckScheduledTasksJob;
use App\Jobs\DispatchScheduledHealthChecksJob;
use App\Jobs\EvaluateAlertRulesJob;
use App\Jobs\ScrapeAllMetricTargetsJob;
use App\Jobs\SyncAllGitHubIntegrationsJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DispatchScheduledHealthChecksJob)->everyMinute();
Schedule::job(new EvaluateAlertRulesJob)->everyMinute();
Schedule::job(new CheckScheduledTasksJob)->everyMinute();
Schedule::job(new ScrapeAllMetricTargetsJob)->everyMinute();
Schedule::job(new SyncAllGitHubIntegrationsJob)->everyFifteenMinutes();
