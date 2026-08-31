<?php

namespace App\Http\Resources\Events;

use App\Http\Resources\Events\Concerns\SerialisesAttendeeMembers;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
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
        // Whether a Game repeats a pairing is an Organiser's business: it is
        // what they check before publishing a Draft, and a Player reading that
        // their table is a rematch learns something about how the field was
        // paired that they were never meant to be told. Non-organisers are not
        // sent the key at all, rather than sent a false — and the five-way
        // self-join behind it is not run for them either.
        $isOrganiser = $this->event->isOrganisedBy($request->user('sanctum'));
        $rematches = $isOrganiser ? $this->rematchGameIds() : [];

        $scoreTypes = $this->event->scoreTypes->sortBy('display_order')->values();

        // Resolved here rather than sent raw, so exactly one column is marked
        // however the Event was set up: a listing has room for one number per
        // team, and a client should not have to pick which when nobody has.
        $primary = $this->event->primaryScoreType();

        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'status' => $this->status->value,
            // The columns every Game in this Round is scored on, whether or
            // not a result has landed yet. Sent with the Round rather than
            // inferred from the scores, so an unplayed Game still knows how
            // many numbers it is waiting for.
            'score_types' => $scoreTypes->map(fn (EventScoreType $type): array => [
                'slug' => $type->slug,
                'name' => $type->name,
                // Which column a Game listing leads with, where it has room
                // for one. Everything else is read on the Game itself.
                'is_primary' => $type->id === $primary?->id,
            ])->all(),
            'games' => $this->games->map(function (Game $game) use ($isOrganiser, $rematches, $scoreTypes): array {
                $scoresByAttendee = $game->scores
                    ->groupBy('event_attendee_id')
                    ->map(fn ($scores) => $scores->mapWithKeys(
                        fn (GameScore $score) => [$score->scoreType->slug => $score->value]
                    ));

                $winner = $game->winningAttendeeId($scoreTypes);

                return [
                    'id' => $game->id,
                    'table_number' => $game->table_number,
                    'is_bye' => $game->is_bye,
                    'is_rematch' => $this->when($isOrganiser, fn (): bool => isset($rematches[$game->id])),
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
                        // Decided here rather than left to the client, which
                        // would have to be told each Score Type's ranking
                        // order and sort direction to work out the same thing.
                        'is_winner' => $attendee->id === $winner,
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
