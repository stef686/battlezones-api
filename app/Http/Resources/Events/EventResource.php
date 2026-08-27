<?php

namespace App\Http\Resources\Events;

use App\Models\Event;
use App\Services\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
class EventResource extends JsonResource
{
    private bool $withViewer = false;

    /**
     * Include what the reader may see and do at this Event.
     *
     * Opt-in rather than automatic: the listing renders many Events, and the
     * viewer block costs a query or two each.
     */
    public function withViewer(): static
    {
        $this->withViewer = true;

        return $this;
    }

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
            // How many Players make up one party, so a registration form knows
            // how many people to ask for.
            'attendee_size' => $this->attendee_size,
            'requires_allegiance' => $this->settings->requiresOpposedAllegiance,
            'registration_closes_at' => $this->registration_closes_at?->toIso8601ZuluString(),
            // Present only where the caller counted, so the listing does not
            // pay for a count nobody reads.
            'attendees_count' => $this->whenCounted('attendees'),
            'is_full' => $this->isFull(),
            'venue' => [
                'name' => $this->venue_name,
                'address' => $this->venue_address,
                'city' => $this->venue_city,
                'country' => $this->venue_country?->value,
            ],
            // Null until an Organiser uploads one, which is what returns the
            // header to the flat surface it is built to sit on.
            'banner' => $this->banner_path === null ? null : [
                'large' => UploadStorage::url($this->banner_path),
                'small' => UploadStorage::url($this->banner_small_path ?? $this->banner_path),
            ],
            'game_system' => GameSystemResource::make($this->whenLoaded('gameSystem')),
            'documents' => EventDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
            ...($this->withViewer ? ['viewer' => EventViewer::for($this->resource, $request->user('sanctum'))] : []),
        ];
    }
}
