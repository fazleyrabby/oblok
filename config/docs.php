<?php

return [
    /*
     * User-facing documentation pages rendered at /docs/{slug}.
     * Each entry maps a URL slug to a Markdown file in the repository.
     */
    'pages' => [
        'setup' => [
            'title' => 'Deploying oblok',
            'summary' => 'Run your own instance with Docker Compose — services, environment, and updates.',
            'file' => 'docs/setup.md',
        ],
        'connect-your-project' => [
            'title' => 'Connect your project',
            'summary' => 'Wire any application into oblok — health checks, metrics, logs, and deployments.',
            'file' => 'docs/connect-your-project.md',
        ],
    ],
];
