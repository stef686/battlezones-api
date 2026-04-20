<?php

namespace App\Http\Resources\Events;

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
                    'user' => [
                        'id' => $attendee->user->id,
                        'name' => $attendee->user->public_name,
                    ],
                    'faction' => $attendee->faction ? [
                        'id' => $attendee->faction->id,
                        'name' => $attendee->faction->name,
                    ] : null,
                    'score' => $attendee->pivot?->score,
                ])->all(),
            ])->all(),
        ];
    }
}
