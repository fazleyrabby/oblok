<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class AiProvider extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'name',
        'endpoint',
        'api_key',
        'models',
        'timeout',
    ];

    protected $casts = [
        'models' => 'array',
        'timeout' => 'integer',
    ];

    /**
     * Get the project that owns the AI provider.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mutator to encrypt the API key when saving.
     */
    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key'] = $value !== null && $value !== ''
            ? Crypt::encryptString($value)
            : null;
    }

    /**
     * Accessor to decrypt the API key when retrieving.
     */
    public function getApiKeyAttribute(?string $value): ?string
    {
        try {
            return $value !== null && $value !== ''
                ? Crypt::decryptString($value)
                : null;
        } catch (DecryptException $e) {
            return null;
        }
    }
}
