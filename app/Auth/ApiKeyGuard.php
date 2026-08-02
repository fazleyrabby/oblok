<?php

namespace App\Auth;

use App\Models\ApiKey;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;

class ApiKeyGuard implements Guard
{
    use GuardHelpers;

    protected ?string $resolvedToken = null;

    public function __construct(UserProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Resolve the authenticated user from the request's bearer token.
     */
    public function user(): ?Authenticatable
    {
        $token = app('request')->bearerToken();

        if ($token === null) {
            return $this->user = null;
        }

        if ($this->user !== null && $this->resolvedToken === $token) {
            return $this->user;
        }

        $key = ApiKey::query()
            ->notRevoked()
            ->where('token', hash('sha256', $token))
            ->first();

        $this->resolvedToken = $token;

        if (! $key || ! $key->isValid()) {
            return $this->user = null;
        }

        app('request')->attributes->set('oblok_api_key', $key);

        $key->increment('requests_count', 1, ['last_used_at' => now()]);

        return $this->user = $this->provider->retrieveById($key->user_id);
    }

    /**
     * API key guards do not accept credentials for manual validation.
     */
    public function validate(array $credentials = []): bool
    {
        return false;
    }
}
