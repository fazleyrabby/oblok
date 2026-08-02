<?php

namespace App\Http\Resources;

use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin User
 *
 * @property ProjectMember $pivot
 */
class ProjectMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->pivot->role->value,
            'added_at' => isset($this->pivot->created_at)
                ? Carbon::make($this->pivot->created_at)->toIso8601String()
                : null,
        ];
    }
}
