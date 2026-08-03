<?php

namespace OblokAgent;

class SystemMetricsCollector
{
    /**
     * Collect Linux /proc or fallback host system metrics (CPU %, Mem %, Disk %).
     *
     * @return array<int, array{name: string, value: float, labels: array<string, string>, timestamp: string}>
     */
    public function collect(): array
    {
        $now = (new \DateTimeImmutable)->format('c');
        $metrics = [];

        // 1. Memory Usage
        $memInfo = $this->readMemInfo();
        if ($memInfo !== null) {
            $total = $memInfo['MemTotal'] ?? 0;
            $free = ($memInfo['MemFree'] ?? 0) + ($memInfo['Buffers'] ?? 0) + ($memInfo['Cached'] ?? 0);
            if ($total > 0) {
                $usedPercent = round((($total - $free) / $total) * 100, 2);
                $metrics[] = [
                    'name' => 'system_memory_usage_percent',
                    'value' => $usedPercent,
                    'labels' => ['type' => 'host'],
                    'timestamp' => $now,
                ];
            }
        }

        // 2. Disk Usage
        $diskTotal = @disk_total_space('/');
        $diskFree = @disk_free_space('/');
        if ($diskTotal !== false && $diskFree !== false && $diskTotal > 0) {
            $diskUsedPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);
            $metrics[] = [
                'name' => 'system_disk_usage_percent',
                'value' => $diskUsedPercent,
                'labels' => ['mount' => '/'],
                'timestamp' => $now,
            ];
        }

        // 3. CPU Load / Usage
        $cpuPercent = $this->readCpuUsage();
        if ($cpuPercent !== null) {
            $metrics[] = [
                'name' => 'system_cpu_usage_percent',
                'value' => $cpuPercent,
                'labels' => ['type' => 'host'],
                'timestamp' => $now,
            ];
        }

        return $metrics;
    }

    /**
     * Parse /proc/meminfo if available.
     *
     * @return array<string, int>|null
     */
    private function readMemInfo(): ?array
    {
        if (! is_readable('/proc/meminfo')) {
            return null;
        }

        $content = @file_get_contents('/proc/meminfo');
        if (! $content) {
            return null;
        }

        $data = [];
        foreach (explode("\n", $content) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)\s+kB/', $line, $m)) {
                $data[$m[1]] = (int) $m[2];
            }
        }

        return $data;
    }

    /**
     * Calculate CPU usage percent from sys_getloadavg or /proc/stat.
     */
    private function readCpuUsage(): ?float
    {
        if (function_exists('sys_getloadavg')) {
            $load = @sys_getloadavg();
            if (is_array($load) && isset($load[0])) {
                // Normalize 1-minute load average to a 0-100 percentage estimate
                return round(min(100.0, $load[0] * 25.0), 2);
            }
        }

        return null;
    }
}
