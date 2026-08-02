<?php

namespace App\Models;

use Database\Factories\HealthCheckResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $service_id
 * @property string $status
 * @property int|null $status_code
 * @property int $response_time_ms
 * @property string|null $error_message
 * @property Carbon $created_at
 */
#[Fillable(['service_id', 'status', 'status_code', 'response_time_ms', 'error_message', 'created_at'])]
class HealthCheckResult extends Model
{
    /** @use HasFactory<HealthCheckResultFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'response_time_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the service that owns the result.
     *
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
