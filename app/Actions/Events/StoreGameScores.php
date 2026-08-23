<?php

namespace App\Actions\Events;

use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class StoreGameScores
{
    /**
     * @param  array<int, array<int, numeric-string|int|float>>  $scoresByAttendee  [attendee_id => [score_type_id => value]]
     */
    public function execute(Game $game, array $scoresByAttendee): void
    {
        $event = $game->round->event;
        $scoreTypes = $event->scoreTypes()->get()->keyBy('id');

        $derivedIds = $scoreTypes->filter(fn (EventScoreType $st) => $st->is_derived)->keys();

        foreach ($scoresByAttendee as $attendeeId => $scores) {
            $suppliedDerived = $derivedIds->intersect(array_keys($scores));

            if ($suppliedDerived->isNotEmpty()) {
                throw new InvalidArgumentException('Cannot supply values for derived score types');
            }

            foreach ($scores as $scoreTypeId => $value) {
                GameScore::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'event_attendee_id' => $attendeeId,
                        'event_score_type_id' => $scoreTypeId,
                    ],
                    ['value' => $value],
                );
            }
        }

        $this->computeDerivedScores($game, $scoresByAttendee, $scoreTypes);
    }

    /**
     * @param  Collection<int, EventScoreType>  $scoreTypes
     */
    private function computeDerivedScores(Game $game, array $scoresByAttendee, $scoreTypes): void
    {
        $derivedTypes = $scoreTypes->filter(fn (EventScoreType $st) => $st->is_derived && $st->win_points !== null);

        if ($derivedTypes->isEmpty()) {
            return;
        }

        $submittedTypes = $scoreTypes->filter(fn (EventScoreType $st) => ! $st->is_derived);
        $primarySubmitted = $submittedTypes->sortBy('display_order')->first();

        if (! $primarySubmitted) {
            return;
        }

        $attendeeIds = array_keys($scoresByAttendee);

        if ($game->is_bye) {
            $this->awardByeWin($game);

            return;
        }

        if (count($attendeeIds) !== 2) {
            return;
        }

        $scores = [];
        foreach ($attendeeIds as $attendeeId) {
            $scores[$attendeeId] = GameScore::query()
                ->where('game_id', $game->id)
                ->where('event_attendee_id', $attendeeId)
                ->where('event_score_type_id', $primarySubmitted->id)
                ->value('value');
        }

        [$a, $b] = $attendeeIds;

        foreach ($derivedTypes as $derivedType) {
            $aPoints = $this->resolveMatchPoints($scores[$a], $scores[$b], $derivedType);
            $bPoints = $this->resolveMatchPoints($scores[$b], $scores[$a], $derivedType);

            foreach ([[$a, $aPoints], [$b, $bPoints]] as [$attendeeId, $points]) {
                GameScore::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'event_attendee_id' => $attendeeId,
                        'event_score_type_id' => $derivedType->id,
                    ],
                    ['value' => $points],
                );
            }
        }
    }

    /**
     * Award a Bye its win.
     *
     * There is no opponent to compare against, so the Match Points come from
     * `is_bye` rather than from two scores. This runs when the Bye is paired
     * rather than waiting on an Organiser: the win is a fact of the draw, and
     * an Attendee should not sit below their true position in the Standings
     * until someone remembers to type their Victory Points in.
     */
    public function awardByeWin(Game $game): void
    {
        $derivedTypes = $game->round->event->scoreTypes()
            ->where('is_derived', true)
            ->whereNotNull('win_points')
            ->get();

        foreach ($game->attendees()->pluck('event_attendees.id') as $attendeeId) {
            foreach ($derivedTypes as $derivedType) {
                GameScore::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'event_attendee_id' => $attendeeId,
                        'event_score_type_id' => $derivedType->id,
                    ],
                    ['value' => $derivedType->win_points],
                );
            }
        }
    }

    private function resolveMatchPoints(string $myScore, string $theirScore, EventScoreType $type): string
    {
        return match (true) {
            bccomp($myScore, $theirScore, 2) > 0 => $type->win_points,
            bccomp($myScore, $theirScore, 2) === 0 => $type->draw_points,
            default => $type->loss_points,
        };
    }
}
