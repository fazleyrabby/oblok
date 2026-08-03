<?php

namespace Tests\Unit\Monitoring;

use App\Models\Service;
use App\Services\Monitoring\DnsHealthChecker;
use App\Services\Monitoring\HealthCheckerRegistry;
use App\Services\Monitoring\HttpHealthChecker;
use App\Services\Monitoring\TcpHealthChecker;
use App\Services\Monitoring\TlsHealthChecker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdvancedHealthCheckTest extends TestCase
{
    public function test_health_checker_registry_resolves_correct_checkers(): void
    {
        $registry = app(HealthCheckerRegistry::class);

        $this->assertInstanceOf(HttpHealthChecker::class, $registry->resolve('http'));
        $this->assertInstanceOf(TcpHealthChecker::class, $registry->resolve('tcp'));
        $this->assertInstanceOf(TlsHealthChecker::class, $registry->resolve('tls'));
        $this->assertInstanceOf(DnsHealthChecker::class, $registry->resolve('dns'));
    }

    public function test_http_checker_validates_response_body_pattern(): void
    {
        Http::fake([
            'https://api.example.com/health' => Http::response('{"status": "ok", "version": "1.2.0"}', 200),
        ]);

        $checker = new HttpHealthChecker;

        $serviceWithMatch = new Service([
            'target' => 'https://api.example.com/health',
            'timeout' => 5,
            'expected_status_code' => 200,
            'config' => ['expected_body_pattern' => '"status": "ok"'],
        ]);

        $resultMatch = $checker->check($serviceWithMatch);
        $this->assertEquals('healthy', $resultMatch->status);
        $this->assertNull($resultMatch->errorMessage);

        $serviceWithMismatch = new Service([
            'target' => 'https://api.example.com/health',
            'timeout' => 5,
            'expected_status_code' => 200,
            'config' => ['expected_body_pattern' => '"status": "error"'],
        ]);

        $resultMismatch = $checker->check($serviceWithMismatch);
        $this->assertEquals('failing', $resultMismatch->status);
        $this->assertStringContainsString('did not contain expected substring', $resultMismatch->errorMessage);
    }

    public function test_tcp_checker_handles_connection_probes(): void
    {
        $checker = new TcpHealthChecker;

        // 127.0.0.1 on an unassigned port should fail TCP connection
        $serviceFailing = new Service([
            'target' => '127.0.0.1:59999',
            'timeout' => 1,
        ]);

        $result = $checker->check($serviceFailing);
        $this->assertEquals('failing', $result->status);
        $this->assertStringContainsString('TCP connection failed', $result->errorMessage);
    }

    public function test_dns_checker_handles_query(): void
    {
        $checker = new DnsHealthChecker;

        $service = new Service([
            'target' => 'google.com',
            'timeout' => 5,
            'config' => ['record_type' => 'A'],
        ]);

        $result = $checker->check($service);
        $this->assertEquals('healthy', $result->status);
    }
}
