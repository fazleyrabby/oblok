<?php

namespace App\Models;

use Database\Factories\LogEntryFactory;
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
 * @property string $level
 * @property string $message
 * @property array<string, mixed>|null $context
 * @property string $channel
 * @property Carbon $created_at
 */
#[Fillable(['project_id', 'level', 'message', 'context', 'channel', 'created_at'])]
class LogEntry extends Model
{
    /** @use HasFactory<LogEntryFactory> */
    use HasFactory, HasUuids;

    protected $table = 'logs';

    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the log entry.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope a query to filter logs by severity level.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeLevel(Builder $query, string $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to search log messages.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where('message', 'like', "%{$term}%");
    }

    /**
     * Determine if the log entry is an error or critical level.
     */
    public function isError(): bool
    {
        return in_array($this->level, ['error', 'critical', 'alert', 'emergency']);
    }
}
