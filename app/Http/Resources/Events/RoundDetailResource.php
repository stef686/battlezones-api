<?php

namespace App\Http\Resources\Events;

use App\Http\Resources\Events\Concerns\SerialisesAttendeeMembers;
use App\Models\EventAttendee;
use App\Models\Game;
use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Round
 */
class RoundDetailResource extends JsonResource
{
    use SerialisesAttendeeMembers;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'games' => $this->games->map(fn (Game $game): array => [
                'id' => $game->id,
                'table_number' => $game->table_number,
                'is_bye' => $game->is_bye,
                'attendees' => $game->attendees->map(fn (EventAttendee $attendee): array => [
                    'id' => $attendee->id,
                    'name' => $attendee->displayName(),
                    'members' => $this->serialiseMembers($attendee),
                    'score' => $attendee->pivot?->score,
                ])->all(),
            ])->all(),
        ];
    }
}
