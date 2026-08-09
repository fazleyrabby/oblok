<?php

namespace App\Actions\Runbooks;

use App\Models\Runbook;

class UpdateRunbook
{
    /**
     * Update an existing operational runbook.
     *
     * @param  array{
     *     name?: string,
     *     description?: string|null,
     *     type?: string,
     *     config?: array<string, mixed>|null,
     *     trigger_type?: string,
     *     enabled?: bool,
     *     cooldown_minutes?: int,
     *     timeout_seconds?: int
     * }  $data
     */
    public function handle(Runbook $runbook, array $data): Runbook
    {
        $runbook->update(array_filter($data, fn ($value) => $value !== null));

        return $runbook->fresh();
    }
}
