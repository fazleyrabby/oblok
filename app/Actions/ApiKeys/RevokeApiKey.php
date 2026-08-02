<?php

namespace App\Actions\ApiKeys;

use App\Models\ApiKey;

class RevokeApiKey
{
    /**
     * Revoke an API key so it can no longer authenticate requests.
     */
    public function handle(ApiKey $key): void
    {
        $key->forceFill(['revoked_at' => now()])->save();
    }
}
