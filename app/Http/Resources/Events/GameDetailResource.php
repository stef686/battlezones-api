<?php

namespace App\Http\Resources\Events;

use App\Models\EventAttendee;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Game
 */
class GameDetailResource extends JsonResource
{
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
                'user' => [
                    'id' => $attendee->user->id,
                    'name' => $attendee->user->public_name,
                ],
                'faction' => $attendee->faction ? [
                    'id' => $attendee->faction->id,
                    'name' => $attendee->faction->name,
                ] : null,
                'army_list' => $attendee->army_list,
                'score' => $attendee->pivot?->score,
            ])->all(),
        ];
    }
}
