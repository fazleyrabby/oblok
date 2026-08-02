<?php

namespace OblokAgent;

class Runner
{
    public function __construct(
        private readonly Config $config,
        private readonly ApiClient $client,
    ) {}

    /**
     * Tail configured files and forward logs and request metrics to oblok.
     */
    public function run(): void
    {
        $logParser = new LogLineParser;
        $accessParser = new AccessLogParser;
        $aggregator = new RequestMetricsAggregator;

        $tailers = [];

        $accessTailer = $this->config->accessLogFile !== null
            ? new FileTailer($this->config->accessLogFile)
            : null;

        $lastFlush = microtime(true);

        while (true) {
            foreach ($this->config->resolveLogFiles() as $file) {
                if (! isset($tailers[$file])) {
                    $tailers[$file] = new FileTailer($file);
                }
            }

            foreach ($tailers as $tailer) {
                foreach ($tailer->readNewLines() as $line) {
                    $entry = $logParser->parse($line);
                    $this->client->pushLog($entry['message'], $entry['level'], $entry['context'], $entry['channel']);
                }
            }

            if ($accessTailer !== null) {
                foreach ($accessTailer->readNewLines() as $line) {
                    $request = $accessParser->parse($line);

                    if ($request !== null) {
                        $aggregator->add($request);
                    }
                }
            }

            if ((microtime(true) - $lastFlush) >= $this->config->flushInterval) {
                $metrics = $aggregator->flush('http_requests', (new \DateTimeImmutable)->format('c'));

                if ($metrics !== []) {
                    $this->client->pushMetrics($metrics);
                }

                $lastFlush = microtime(true);
            }

            sleep($this->config->pollInterval);
        }
    }
}
