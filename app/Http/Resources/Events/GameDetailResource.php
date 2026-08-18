<?php

namespace App\Http\Resources\Events;

use App\Http\Resources\Events\Concerns\SerialisesAttendeeMembers;
use App\Models\EventAttendee;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Game
 */
class GameDetailResource extends JsonResource
{
    use SerialisesAttendeeMembers;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'table_number' => $this->table_number,
            'is_bye' => $this->is_bye,
            'round' => [
                'id' => $this->round->id,
                'number' => $this->round->number,
                'name' => $this->round->name,
            ],
            'attendees' => $this->attendees->map(fn (EventAttendee $attendee): array => [
                'id' => $attendee->id,
                'name' => $attendee->displayName(),
                'members' => $this->serialiseMembers($attendee, withArmyList: true),
                'score' => $attendee->pivot?->score,
            ])->all(),
        ];
    }
}
