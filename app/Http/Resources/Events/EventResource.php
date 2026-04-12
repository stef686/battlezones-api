<?php

namespace App\Http\Resources\Events;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
class EventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status->value,
            'pairing_format' => $this->pairing_format->value,
            'starts_at' => $this->starts_at->toIso8601ZuluString(),
            'ends_at' => $this->ends_at->toIso8601ZuluString(),
            'max_attendees' => $this->max_attendees,
            'venue' => [
                'name' => $this->venue_name,
                'address' => $this->venue_address,
                'city' => $this->venue_city,
                'country' => $this->venue_country?->value,
            ],
            'game_system' => GameSystemResource::make($this->whenLoaded('gameSystem')),
            'documents' => EventDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
