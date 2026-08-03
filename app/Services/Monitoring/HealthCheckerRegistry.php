<?php

namespace App\Services\Monitoring;

use App\Models\Service;
use App\Services\Monitoring\Contracts\HealthCheckerInterface;
use InvalidArgumentException;

class HealthCheckerRegistry
{
    /**
     * @var array<string, HealthCheckerInterface>
     */
    protected array $checkers = [];

    public function __construct(
        HttpHealthChecker $httpChecker,
        TcpHealthChecker $tcpChecker,
        TlsHealthChecker $tlsChecker,
        DnsHealthChecker $dnsChecker,
    ) {
        $this->register('http', $httpChecker);
        $this->register('tcp', $tcpChecker);
        $this->register('tls', $tlsChecker);
        $this->register('dns', $dnsChecker);
    }

    /**
     * Register a health checker instance for a type.
     */
    public function register(string $type, HealthCheckerInterface $checker): void
    {
        $this->checkers[strtolower($type)] = $checker;
    }

    /**
     * Resolve a health checker for the given service type.
     *
     * @throws InvalidArgumentException
     */
    public function resolve(string $type): HealthCheckerInterface
    {
        $normalizedType = strtolower($type);

        if (! isset($this->checkers[$normalizedType])) {
            throw new InvalidArgumentException("Unsupported health check type: {$type}");
        }

        return $this->checkers[$normalizedType];
    }

    /**
     * Perform health check for the given service using appropriate checker.
     */
    public function check(Service $service): HealthCheckResultData
    {
        return $this->resolve($service->type)->check($service);
    }
}
