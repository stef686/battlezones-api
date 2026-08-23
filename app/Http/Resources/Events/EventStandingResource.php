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
            'attendee' => [
                'id' => $this->attendee->id,
                'name' => $this->attendee->displayName(),
                'members' => $this->serialiseMembers($this->attendee, withClubs: true),
            ],
            'scores' => $this->scores->map(fn (array $score): array => [
                'value' => $score['value'],
                'score_type' => [
                    'id' => $score['scoreType']->id,
                    'name' => $score['scoreType']->name,
                    'slug' => $score['scoreType']->slug,
                    'sort_direction' => $score['scoreType']->sort_direction->value,
                ],
            ])->values(),
        ];
    }
}
