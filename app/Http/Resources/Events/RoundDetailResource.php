<?php

namespace App\Http\Resources\Events;

use App\Http\Resources\Events\Concerns\SerialisesAttendeeMembers;
use App\Models\EventAttendee;
use App\Models\Game;
use App\Models\GameScore;
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
        $rematches = $this->rematchGameIds();

        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'status' => $this->status->value,
            'games' => $this->games->map(function (Game $game) use ($rematches): array {
                $scoresByAttendee = $game->scores
                    ->groupBy('event_attendee_id')
                    ->map(fn ($scores) => $scores->mapWithKeys(
                        fn (GameScore $score) => [$score->scoreType->slug => $score->value]
                    ));

                return [
                    'id' => $game->id,
                    'table_number' => $game->table_number,
                    'is_bye' => $game->is_bye,
                    'is_rematch' => isset($rematches[$game->id]),
                    // Which tables are still playing is what holds up the next
                    // Round, so an Organiser reviewing a Round can see it here
                    // rather than opening every Game in turn.
                    'result' => [
                        'submitted_at' => $game->submitted_at?->toIso8601ZuluString(),
                        'is_flagged' => $game->openResultFlag !== null,
                    ],
                    'attendees' => $game->attendees->map(fn (EventAttendee $attendee): array => [
                        'id' => $attendee->id,
                        'name' => $attendee->displayName(),
                        // The review screen has to be able to see at a glance
                        // that every Game is opposed.
                        'allegiance' => $attendee->allegiance?->value,
                        'members' => $this->serialiseMembers($attendee),
                        'scores' => $scoresByAttendee->get($attendee->id, collect())->toArray(),
                    ])->all(),
                ];
            })->all(),
        ];
    }
}
