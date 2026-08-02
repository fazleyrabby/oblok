<?php

namespace App\Enums;

enum AlertMetric: string
{
    case ServiceHealth = 'service_health';
    case QueueBacklog = 'queue_backlog';
    case DeploymentStatus = 'deployment_status';
    case IncidentOpened = 'incident_opened';

    public function label(): string
    {
        return match ($this) {
            self::ServiceHealth => 'Service Health',
            self::QueueBacklog => 'Queue Backlog',
            self::DeploymentStatus => 'Deployment Status',
            self::IncidentOpened => 'Incident Opened',
        };
    }

    /**
     * Comparisons valid for this metric.
     *
     * @return array<int, AlertComparison>
     */
    public function availableComparisons(): array
    {
        return match ($this) {
            self::ServiceHealth => [AlertComparison::Equals, AlertComparison::NotEquals],
            self::QueueBacklog => [AlertComparison::Gt, AlertComparison::Lt],
            self::DeploymentStatus => [AlertComparison::Equals, AlertComparison::NotEquals],
            self::IncidentOpened => [AlertComparison::Equals, AlertComparison::NotEquals],
        };
    }

    public function requiresThreshold(): bool
    {
        return $this === self::QueueBacklog;
    }
}
