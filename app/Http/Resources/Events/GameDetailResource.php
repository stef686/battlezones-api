<?php

namespace App\Http\Resources\Events;

use App\Http\Resources\Events\Concerns\SerialisesAttendeeMembers;
use App\Models\EventAttendee;
use App\Models\Game;
use App\Models\GameScore;
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
        $scoresByAttendee = $this->scores
            ->groupBy('event_attendee_id')
            ->map(fn ($scores) => $scores->mapWithKeys(
                fn (GameScore $score) => [$score->scoreType->slug => $score->value]
            ));

        return [
            'id' => $this->id,
            'table_number' => $this->table_number,
            'is_bye' => $this->is_bye,
            'round' => [
                'id' => $this->round->id,
                'number' => $this->round->number,
                'name' => $this->round->name,
            ],
            'result' => [
                'submitted_at' => $this->submitted_at?->toIso8601String(),
                'submitted_by' => $this->whenLoaded('submittedBy', fn (): ?array => $this->submittedBy === null ? null : [
                    'id' => $this->submittedBy->id,
                    'name' => $this->submittedBy->name,
                ]),
                'edited_at' => $this->edited_at?->toIso8601String(),
                'edited_by' => $this->whenLoaded('editedBy', fn (): ?array => $this->editedBy === null ? null : [
                    'id' => $this->editedBy->id,
                    'name' => $this->editedBy->name,
                ]),
                'is_flagged' => $this->openResultFlag !== null,
            ],
            'attendees' => $this->attendees->map(fn (EventAttendee $attendee): array => [
                'id' => $attendee->id,
                'name' => $attendee->displayName(),
                'members' => $this->serialiseMembers($attendee, withArmyList: true),
                'scores' => $scoresByAttendee->get($attendee->id, collect())->toArray(),
            ])->all(),
        ];
    }
}
