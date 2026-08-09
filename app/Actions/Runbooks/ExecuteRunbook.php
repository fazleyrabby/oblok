<?php

namespace App\Actions\Runbooks;

use App\Enums\RunbookType;
use App\Models\Runbook;
use App\Models\RunbookRun;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Throwable;

class ExecuteRunbook
{
    /**
     * Execute a runbook and log execution result to RunbookRun.
     */
    public function handle(Runbook $runbook, string $triggeredByType = 'manual', ?string $triggeredById = null): RunbookRun
    {
        $run = RunbookRun::create([
            'runbook_id' => $runbook->id,
            'project_id' => $runbook->project_id,
            'triggered_by_type' => $triggeredByType,
            'triggered_by_id' => $triggeredById,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $startTime = microtime(true);

        try {
            match ($runbook->type) {
                RunbookType::Artisan => $this->executeArtisan($runbook, $run),
                RunbookType::Webhook => $this->executeWebhook($runbook, $run),
                RunbookType::Shell => $this->executeShell($runbook, $run),
            };

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            $runbook->update(['last_executed_at' => now()]);

            $run->refresh();
            if ($run->duration_ms === null) {
                $run->update(['duration_ms' => $durationMs]);
            }
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            $run->markFailed("Execution Exception: {$e->getMessage()}", 1, $durationMs);
        }

        return $run->fresh();
    }

    /**
     * Execute an Artisan command runbook.
     */
    protected function executeArtisan(Runbook $runbook, RunbookRun $run): void
    {
        $command = $runbook->config['command'] ?? 'cache:clear';
        $params = $runbook->config['parameters'] ?? [];

        // Whitelist / safety check: allow standard safe artisan commands
        $exitCode = Artisan::call($command, $params);
        $output = Artisan::output();

        if ($exitCode === 0) {
            $run->markSuccessful($output, $exitCode);
        } else {
            $run->markFailed($output ?: "Artisan command [{$command}] failed with exit code {$exitCode}", $exitCode);
        }
    }

    /**
     * Execute an HTTP Webhook runbook.
     */
    protected function executeWebhook(Runbook $runbook, RunbookRun $run): void
    {
        $url = $runbook->config['url'] ?? '';
        $method = strtoupper($runbook->config['method'] ?? 'POST');
        $headers = $runbook->config['headers'] ?? [];
        $body = $runbook->config['body'] ?? null;
        $timeout = $runbook->timeout_seconds ?? 30;

        if (empty($url)) {
            $run->markFailed('Webhook URL is empty.', 400);

            return;
        }

        $request = Http::timeout($timeout)->withHeaders($headers);

        $response = match ($method) {
            'GET' => $request->get($url),
            'PUT' => $request->put($url, is_array($body) ? $body : json_decode($body ?: '{}', true)),
            'DELETE' => $request->delete($url),
            default => $request->post($url, is_array($body) ? $body : json_decode($body ?: '{}', true)),
        };

        $output = "HTTP {$response->status()}\n".$response->body();

        if ($response->successful()) {
            $run->markSuccessful($output, $response->status());
        } else {
            $run->markFailed($output, $response->status());
        }
    }

    /**
     * Execute a Shell process runbook.
     */
    protected function executeShell(Runbook $runbook, RunbookRun $run): void
    {
        $command = $runbook->config['command'] ?? 'echo "Runbook executed"';
        $timeout = $runbook->timeout_seconds ?? 30;

        $result = Process::timeout($timeout)->run($command);

        $output = trim($result->output()."\n".$result->errorOutput());

        if ($result->successful()) {
            $run->markSuccessful($output ?: 'Process completed successfully.', $result->exitCode());
        } else {
            $run->markFailed($output ?: "Process failed with exit code {$result->exitCode()}", $result->exitCode() ?: 1);
        }
    }
}
