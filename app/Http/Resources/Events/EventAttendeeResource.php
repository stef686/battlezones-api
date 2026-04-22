<?php

namespace App\Http\Resources\Events;

use App\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventAttendee
 */
class EventAttendeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->public_name,
            ],
            'faction' => $this->faction ? [
                'id' => $this->faction->id,
                'name' => $this->faction->name,
            ] : null,
            'clubs' => $this->user->clubs->map(fn ($club) => [
                'id' => $club->id,
                'name' => $club->name,
            ])->values(),
        ];
    }
}
