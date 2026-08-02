<?php

namespace App\Actions\ApiKeys;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CreateApiKey
{
    /**
     * Create a new API key and return the plaintext token (shown only once).
     *
     * @return array{key: ApiKey, token: string}
     */
    public function handle(User $user, Project $project, string $name, ?Carbon $expiresAt = null): array
    {
        $token = config('oblok.api_keys.prefix').Str::random(36);

        $key = ApiKey::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'name' => $name,
            'token' => hash('sha256', $token),
            'key_prefix' => Str::substr($token, 0, 12),
            'expires_at' => $expiresAt,
        ]);

        return ['key' => $key, 'token' => $token];
    }
}
