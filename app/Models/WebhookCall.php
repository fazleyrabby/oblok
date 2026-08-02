<?php

namespace App\Models;

use Database\Factories\WebhookCallFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string|null $event
 * @property string $method
 * @property string|null $url
 * @property int|null $status_code
 * @property array<string, mixed>|null $request_headers
 * @property array<string, mixed>|null $request_payload
 * @property array<string, mixed>|null $response_payload
 * @property int|null $processing_time_ms
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $replay_count
 * @property Carbon|null $replayed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'project_id', 'event', 'method', 'url', 'status_code', 'request_headers',
    'request_payload', 'response_payload', 'processing_time_ms', 'ip_address',
    'user_agent', 'replay_count', 'replayed_at',
])]
class WebhookCall extends Model
{
    /** @use HasFactory<WebhookCallFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'request_headers' => 'array',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'processing_time_ms' => 'integer',
            'replay_count' => 'integer',
            'replayed_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the webhook call.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope a query to webhook calls for a given event.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeOfEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }
}
