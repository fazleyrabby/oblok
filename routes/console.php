<?php

use App\Jobs\DispatchScheduledHealthChecksJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DispatchScheduledHealthChecksJob)->everyMinute();
