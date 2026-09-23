<?php

namespace App\Http\Resources\Events;

use App\Http\Resources\Events\Concerns\SerialisesAttendeeMembers;
use App\Queries\Standing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Standing
 */
class EventStandingResource extends JsonResource
{
    use SerialisesAttendeeMembers;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->attendee->id,
            'position' => $this->position,
            // Places gained since the Round before the one being played, so a
            // reader sees who is climbing rather than only who is ahead. Null
            // until two Rounds have results to compare, which is a table with
            // no arrows rather than a table full of dashes.
            'movement' => $this->movement(),
            'attendee' => [
                'id' => $this->attendee->id,
                'name' => $this->attendee->displayName(),
                'avatar' => $this->attendee->avatarUrl(),
                'members' => $this->serialiseMembers($this->attendee, withClubs: true),
            ],
            'scores' => $this->scores->map(fn (array $score): array => [
                'value' => $score['value'],
                'score_type' => [
                    'id' => $score['scoreType']->id,
                    'name' => $score['scoreType']->name,
                    'abbreviation' => $score['scoreType']->abbreviation,
                    'slug' => $score['scoreType']->slug,
                    'sort_direction' => $score['scoreType']->sort_direction->value,
                ],
            ])->values(),
        ];
    }
}
