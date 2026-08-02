<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scheduler Monitoring
    |--------------------------------------------------------------------------
    |
    | Missed-run detection treats a scheduled task as missed when its next
    | run time passes without a recorded run, plus a grace period to absorb
    | minor scheduling drift.
    |
    */

    'scheduler' => [
        'missed_grace_minutes' => env('ATLAS_SCHEDULER_MISSED_GRACE_MINUTES', 5),
    ],

];
