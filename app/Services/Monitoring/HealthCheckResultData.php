<?php

namespace App\Services\Monitoring;

readonly class HealthCheckResultData
{
    public function __construct(
        public string $status,
        public ?int $statusCode,
        public int $responseTimeMs,
        public ?string $errorMessage = null,
    ) {}
}
