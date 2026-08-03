<?php

namespace App\Services\Monitoring;

use App\Models\Service;
use App\Services\Monitoring\Contracts\HealthCheckerInterface;
use Throwable;

class DnsHealthChecker implements HealthCheckerInterface
{
    public function check(Service $service): HealthCheckResultData
    {
        $startTime = microtime(true);
        $target = $service->target;

        $config = is_array($service->config) ? $service->config : [];
        $recordType = strtoupper((string) ($config['record_type'] ?? 'A'));
        $expectedValue = isset($config['expected_value']) ? (string) $config['expected_value'] : null;

        $typeConst = match ($recordType) {
            'AAAA' => DNS_AAAA,
            'CNAME' => DNS_CNAME,
            'MX' => DNS_MX,
            'TXT' => DNS_TXT,
            default => DNS_A,
        };

        try {
            $records = @dns_get_record($target, $typeConst);
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($records === false || empty($records)) {
                return new HealthCheckResultData(
                    status: 'failing',
                    statusCode: null,
                    responseTimeMs: $durationMs,
                    errorMessage: "DNS record query failed or returned no records for {$target} ({$recordType})",
                );
            }

            if ($expectedValue !== null && $expectedValue !== '') {
                $matched = false;
                foreach ($records as $rec) {
                    $val = $rec['ip'] ?? $rec['ipv6'] ?? $rec['target'] ?? $rec['txt'] ?? null;
                    if (is_string($val) && (str_contains($val, $expectedValue) || $val === $expectedValue)) {
                        $matched = true;
                        break;
                    }
                }

                if (! $matched) {
                    return new HealthCheckResultData(
                        status: 'failing',
                        statusCode: null,
                        responseTimeMs: $durationMs,
                        errorMessage: "DNS record {$recordType} for {$target} did not match expected value: {$expectedValue}",
                    );
                }
            }

            return new HealthCheckResultData(
                status: 'healthy',
                statusCode: null,
                responseTimeMs: $durationMs,
                errorMessage: null,
            );
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            return new HealthCheckResultData(
                status: 'failing',
                statusCode: null,
                responseTimeMs: $durationMs,
                errorMessage: "DNS lookup error: {$e->getMessage()}",
            );
        }
    }
}
