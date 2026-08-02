<?php

namespace App\Services\Metrics;

use App\Services\Metrics\Exceptions\PrometheusParseException;
use Illuminate\Support\Carbon;

class PrometheusExpositionParser
{
    /**
     * Parse Prometheus text exposition format into metric samples.
     *
     * @return array<int, array{name: string, labels: array<string, string>, value: float, recorded_at: string|null}>
     *
     * @throws PrometheusParseException
     */
    public function parse(string $body): array
    {
        $samples = [];

        foreach (preg_split('/\r?\n/', $body) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (! preg_match('/^([a-zA-Z_:][a-zA-Z0-9_:]*)(\{[^}]*\})?\s+([-+]?[0-9]*\.?[0-9]+(?:[eE][-+]?[0-9]+)?)(?:\s+([0-9]+(?:\.[0-9]+)?))?$/', $line, $matches)) {
                continue;
            }

            $name = $matches[1];
            $labels = $matches[2] !== '' ? $this->parseLabels($matches[2]) : [];
            $value = (float) $matches[3];
            $timestampMs = isset($matches[4]) ? $matches[4] : null;

            $samples[] = [
                'name' => $name,
                'labels' => $labels,
                'value' => $value,
                'recorded_at' => $timestampMs !== null
                    ? Carbon::createFromTimestampMs((int) $timestampMs)->toIso8601String()
                    : null,
            ];
        }

        return $samples;
    }

    /**
     * Parse a label set such as `{code="200",method="GET"}`.
     *
     * @return array<string, string>
     *
     * @throws PrometheusParseException
     */
    protected function parseLabels(string $segment): array
    {
        $labels = [];

        if (preg_match_all('/([a-zA-Z_:][a-zA-Z0-9_:]*)="((?:\\\\.|[^"\\\\])*)"/', $segment, $matches, PREG_SET_ORDER) === false) {
            throw new PrometheusParseException('Malformed label set: '.$segment);
        }

        foreach ($matches as $match) {
            $labels[$match[1]] = stripcslashes($match[2]);
        }

        return $labels;
    }
}
