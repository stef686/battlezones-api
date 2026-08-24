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
        $reader = $request->user();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'votes_per_player' => $this->votes_per_player,
            'opens_at' => $this->opens_at?->toIso8601String(),
            'closes_at' => $this->closes_at?->toIso8601String(),
            'is_open' => $this->isOpen(),
            'is_open_for_me' => $reader === null ? null : $this->isOpenFor($reader),
            // This reader's own picks, so revising a Ballot starts from what
            // they last sent rather than from an empty screen. Never anybody
            // else's: a Ballot is secret, and a tally is organiser-only.
            'my_ballot' => $reader === null ? [] : $this->votes()
                ->where('voter_user_id', $reader->getKey())
                ->pluck('subject_event_attendee_id')
                ->map(intval(...))
                ->values()
                ->all(),
        ];
    }
}
