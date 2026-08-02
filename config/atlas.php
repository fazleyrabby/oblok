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

    /*
    |--------------------------------------------------------------------------
    | GitHub Integration
    |--------------------------------------------------------------------------
    |
    | Controls the GitHub API client used to fetch repository context. The
    | API URL may point to a GitHub Enterprise instance.
    |
    */

    'github' => [
        'api_url' => env('GITHUB_API_URL', 'https://api.github.com'),
        'timeout' => env('GITHUB_API_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Keys
    |--------------------------------------------------------------------------
    |
    | Machine-to-machine access for the REST API. Tokens are generated with a
    | configurable prefix and rate-limited per key against the API limits.
    |
    */

    'api_keys' => [
        'prefix' => env('ATLAS_API_KEY_PREFIX', 'atl_'),
    ],

    'api' => [
        'rate_limit' => env('ATLAS_API_RATE_LIMIT', 120),
    ],

];
