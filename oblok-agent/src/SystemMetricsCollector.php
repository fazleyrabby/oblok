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

        // 1. Host Memory Usage
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

        // 2. Container Memory Usage (cgroups v1 / v2)
        $containerMem = $this->readContainerMemory();
        if ($containerMem !== null) {
            $metrics[] = [
                'name' => 'container_memory_usage_percent',
                'value' => $containerMem['percent'],
                'labels' => [
                    'type' => 'container',
                    'used_bytes' => (string) $containerMem['used_bytes'],
                    'limit_bytes' => (string) $containerMem['limit_bytes'],
                ],
                'timestamp' => $now,
            ];
        }

        // 3. Disk Usage
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

        // 4. CPU Load & Cores
        $cpuPercent = $this->readCpuUsage();
        $cpuCores = $this->readCpuCores();
        if ($cpuPercent !== null) {
            $metrics[] = [
                'name' => 'system_cpu_usage_percent',
                'value' => $cpuPercent,
                'labels' => ['type' => 'host', 'cores' => (string) $cpuCores],
                'timestamp' => $now,
            ];
            $metrics[] = [
                'name' => 'system_cpu_cores',
                'value' => (float) $cpuCores,
                'labels' => ['type' => 'host'],
                'timestamp' => $now,
            ];
        }

        return $metrics;
    }

    /**
     * Read Docker container memory usage & limit from cgroups (v1 or v2).
     *
     * @return array{used_bytes: int, limit_bytes: int, percent: float}|null
     */
    private function readContainerMemory(): ?array
    {
        $used = null;
        $limit = null;

        // cgroups v2
        if (is_readable('/sys/fs/cgroup/memory.current')) {
            $usedVal = trim((string) @file_get_contents('/sys/fs/cgroup/memory.current'));
            $limitVal = is_readable('/sys/fs/cgroup/memory.max') ? trim((string) @file_get_contents('/sys/fs/cgroup/memory.max')) : 'max';

            if (is_numeric($usedVal)) {
                $used = (int) $usedVal;
            }

            if (is_numeric($limitVal)) {
                $limit = (int) $limitVal;
            } else {
                // If limit is 'max' (unlimited container limit), fallback to host total memory
                $memInfo = $this->readMemInfo();
                if (isset($memInfo['MemTotal'])) {
                    $limit = $memInfo['MemTotal'] * 1024; // KB to Bytes
                }
            }
        }
        // cgroups v1 fallback
        elseif (is_readable('/sys/fs/cgroup/memory/memory.usage_in_bytes') && is_readable('/sys/fs/cgroup/memory/memory.limit_in_bytes')) {
            $usedVal = trim((string) @file_get_contents('/sys/fs/cgroup/memory/memory.usage_in_bytes'));
            $limitVal = trim((string) @file_get_contents('/sys/fs/cgroup/memory/memory.limit_in_bytes'));

            if (is_numeric($usedVal)) {
                $used = (int) $usedVal;
            }

            if (is_numeric($limitVal)) {
                $limit = (int) $limitVal;
            }
        }

        if ($used !== null && $limit !== null && $limit > 0 && $limit < 9223372036854770000) {
            $percent = round(($used / $limit) * 100, 2);

            return [
                'used_bytes' => $used,
                'limit_bytes' => $limit,
                'percent' => $percent,
            ];
        }

        return null;
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

    /** @var array{user: int, nice: int, system: int, idle: int, iowait: int, irq: int, softirq: int, steal: int}|null */
    private static ?array $lastCpuState = null;

    /**
     * Calculate true CPU usage percentage by measuring delta active vs total ticks from /proc/stat.
     */
    private function readCpuUsage(): ?float
    {
        if (! is_readable('/proc/stat')) {
            return null;
        }

        $content = @file_get_contents('/proc/stat');
        if (! $content) {
            return null;
        }

        $lines = explode("\n", $content);
        if (! isset($lines[0]) || ! str_starts_with($lines[0], 'cpu ')) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($lines[0]));
        if (! $parts || count($parts) < 8) {
            return null;
        }

        $user = (int) $parts[1];
        $nice = (int) $parts[2];
        $system = (int) $parts[3];
        $idle = (int) $parts[4];
        $iowait = (int) ($parts[5] ?? 0);
        $irq = (int) ($parts[6] ?? 0);
        $softirq = (int) ($parts[7] ?? 0);
        $steal = (int) ($parts[8] ?? 0);

        $current = compact('user', 'nice', 'system', 'idle', 'iowait', 'irq', 'softirq', 'steal');

        if (self::$lastCpuState === null) {
            self::$lastCpuState = $current;

            return null; // Need a second sample to compute delta
        }

        $prev = self::$lastCpuState;
        self::$lastCpuState = $current;

        $prevActive = $prev['user'] + $prev['nice'] + $prev['system'] + $prev['irq'] + $prev['softirq'] + $prev['steal'];
        $prevIdle = $prev['idle'] + $prev['iowait'];
        $prevTotal = $prevActive + $prevIdle;

        $curActive = $user + $nice + $system + $irq + $softirq + $steal;
        $curIdle = $idle + $iowait;
        $curTotal = $curActive + $curIdle;

        $totalDiff = $curTotal - $prevTotal;
        $activeDiff = $curActive - $prevActive;

        if ($totalDiff <= 0) {
            return 0.0;
        }

        return round(($activeDiff / $totalDiff) * 100, 2);
    }

    /**
     * Read total CPU core count.
     */
    private function readCpuCores(): int
    {
        if (is_readable('/proc/cpuinfo')) {
            $content = @file_get_contents('/proc/cpuinfo');
            if ($content) {
                $count = preg_match_all('/^processor\s+:/m', $content);
                if ($count > 0) {
                    return $count;
                }
            }
        }

        return 1;
    }
}
