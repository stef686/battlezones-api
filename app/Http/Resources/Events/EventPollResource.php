<?php

namespace App\Http\Resources\Events;

use App\Models\EventPoll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventPoll
 */
class EventPollResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'votes_per_player' => $this->votes_per_player,
            'opens_at' => $this->opens_at?->toIso8601String(),
            'closes_at' => $this->closes_at?->toIso8601String(),
            'is_open' => $this->isOpen(),
        ];
    }
}
