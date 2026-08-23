<?php

namespace App\Http\Resources\Events;

use App\Models\EventInvite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventInvite
 */
class EventInviteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var EventInvite $invite */
        $invite = $this->resource;

        return [
            'id' => $invite->id,
            'role' => $invite->role->value,
            'email' => $invite->user->email,
            'name' => $invite->user->name,
            'is_claimed' => $invite->user->isClaimed(),
            'attendee_id' => $invite->event_attendee_id,
            'event' => $this->whenLoaded('event', fn (): array => [
                'slug' => $invite->event->slug,
                'name' => $invite->event->name,
                'starts_at' => $invite->event->starts_at,
                'ends_at' => $invite->event->ends_at,
            ]),
            'expires_at' => $invite->expires_at,
            'revoked_at' => $invite->revoked_at,
        ];
    }
}
