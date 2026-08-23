<?php

namespace App\Actions\Events;

use App\Enums\Allegiance;
use App\Enums\RoundStatus;
use App\Enums\SortDirection;
use App\Exceptions\CannotGeneratePairings;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Round;
use App\Services\HungarianMatcher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Pair the next Round of an Event and leave it in Draft for review.
 *
 * The field is divided into two sides and matched across them, so the problem
 * is bipartite and exactly solvable. Where the Event opposes Allegiances the
 * sides are the Allegiances themselves; otherwise the ranked field is folded
 * in half.
 */
class GenerateRoundPairings
{
    public function __construct(
        private readonly HungarianMatcher $matcher,
        private readonly StoreGameScores $storeGameScores,
    ) {}

    public function execute(Event $event): Round
    {
        $roundNumber = $this->nextRoundNumber($event);

        $this->guardRoundCount($event, $roundNumber);
        $this->guardTheRoundBefore($event);

        $rankingScoreTypes = $this->rankingScoreTypes($event);
        $totals = $this->scoreTotals($event);

        $ranked = $this->rankAttendees($event, $rankingScoreTypes, $totals);

        $this->guardField($event, $ranked);

        $scoreGroups = $this->scoreGroups($ranked, $rankingScoreTypes->first(), $totals);
        $previousOpponents = $this->previousOpponents($event);

        [$sideA, $sideB, $byes] = $this->divide($event, $ranked, $this->attendeesGivenByes($event));

        $assignment = $this->matcher->solve(
            $this->costMatrix($sideA, $sideB, $scoreGroups, $previousOpponents, $ranked->count())
        );

        $pairs = [];

        foreach ($sideA as $index => $attendee) {
            $pairs[] = [$attendee, $sideB[$assignment[$index]]];
        }

        return $this->persist($event, $roundNumber, $ranked, $pairs, $byes);
    }

    /**
     * An Event that has not declared a round count is open-ended, so it is only
     * a ceiling when one is set.
     */
    private function guardRoundCount(Event $event, int $roundNumber): void
    {
        $roundCount = $event->settings->roundCount;

        if ($roundCount !== null && $roundNumber > $roundCount) {
            throw CannotGeneratePairings::roundCountReached($roundCount);
        }
    }

    private function nextRoundNumber(Event $event): int
    {
        return ((int) $event->rounds()->max('number')) + 1;
    }

    /**
     * A Round can only be paired on finished results.
     *
     * A Draft Round trips the same guard: none of its Games have been played,
     * so pairing past it would strand a Round nobody has seen.
     */
    private function guardTheRoundBefore(Event $event): void
    {
        $latest = $event->rounds()->orderByDesc('number')->first();

        if (! $latest instanceof Round) {
            return;
        }

        if ($latest->isDraft()) {
            throw CannotGeneratePairings::roundNotPublished($latest->number);
        }

        if ($this->hasOutstandingResults($latest)) {
            throw CannotGeneratePairings::resultsOutstanding($latest->number);
        }
    }

    /**
     * Whether any Attendee is still without a score in this Round.
     *
     * Byes are exempt: nothing was contested, and their Match Points come from
     * the Bye itself rather than from a submission that could be outstanding.
     */
    private function hasOutstandingResults(Round $round): bool
    {
        return DB::table('game_attendee')
            ->join('games', 'games.id', '=', 'game_attendee.game_id')
            ->where('games.round_id', $round->getKey())
            ->where('games.is_bye', false)
            ->whereNotExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('game_scores')
                    ->whereColumn('game_scores.game_id', 'game_attendee.game_id')
                    ->whereColumn('game_scores.event_attendee_id', 'game_attendee.event_attendee_id');
            })
            ->exists();
    }

    /**
     * Reject a field no legal Round can be built from, while it is still an
     * Organiser's data problem rather than a Round full of Byes.
     *
     * @param  Collection<int, EventAttendee>  $ranked
     */
    private function guardField(Event $event, Collection $ranked): void
    {
        if ($ranked->count() < 2) {
            throw CannotGeneratePairings::tooFewAttendees();
        }

        if (! $event->settings->requiresOpposedAllegiance) {
            return;
        }

        $withoutAllegiance = $ranked->whereNull('allegiance')->count();

        if ($withoutAllegiance > 0) {
            throw CannotGeneratePairings::allegianceMissing($withoutAllegiance);
        }

        if ($ranked->pluck('allegiance')->unique()->count() < 2) {
            throw CannotGeneratePairings::allegianceOneSided();
        }
    }

    /**
     * The field in Standings order: each ranking Score Type in precedence,
     * then by id so that a given field always ranks the same way.
     *
     * @param  Collection<int, EventScoreType>  $rankingScoreTypes
     * @param  array<int, array<int, float>>  $totals
     * @return Collection<int, EventAttendee>
     */
    private function rankAttendees(Event $event, Collection $rankingScoreTypes, array $totals): Collection
    {
        $attendees = $event->attendees()->orderBy('id')->get();

        $keys = [];

        foreach ($attendees as $attendee) {
            $key = [];

            foreach ($rankingScoreTypes as $scoreType) {
                $total = $totals[$attendee->id][$scoreType->id] ?? 0.0;

                $key[] = $scoreType->sort_direction === SortDirection::Desc ? -$total : $total;
            }

            $key[] = (float) $attendee->id;
            $keys[$attendee->id] = $key;
        }

        return $attendees
            ->sort(fn (EventAttendee $a, EventAttendee $b): int => $keys[$a->id] <=> $keys[$b->id])
            ->values();
    }

    /**
     * @return Collection<int, EventScoreType>
     */
    private function rankingScoreTypes(Event $event): Collection
    {
        if (! $event->relationLoaded('scoreTypes')) {
            $event->load('scoreTypes');
        }

        return $event->scoreTypes
            ->whereNotNull('ranking_order')
            ->sortBy('ranking_order')
            ->values();
    }

    /**
     * Accumulated totals per Attendee per Score Type, in one query.
     *
     * @return array<int, array<int, float>>
     */
    private function scoreTotals(Event $event): array
    {
        return DB::table('game_scores')
            ->join('games', 'games.id', '=', 'game_scores.game_id')
            ->join('rounds', 'rounds.id', '=', 'games.round_id')
            ->where('rounds.event_id', $event->getKey())
            ->groupBy('game_scores.event_attendee_id', 'game_scores.event_score_type_id')
            ->select([
                'game_scores.event_attendee_id',
                'game_scores.event_score_type_id',
                DB::raw('SUM(game_scores.value) as total'),
            ])
            ->get()
            ->groupBy('event_attendee_id')
            ->map(fn ($rows): array => $rows
                ->mapWithKeys(fn ($row): array => [(int) $row->event_score_type_id => (float) $row->total])
                ->all())
            ->all();
    }

    /**
     * The score group each Attendee sits in, zero being the top.
     *
     * Grouping is on the leading ranking Score Type — Match Points, not the
     * tiebreakers — so that a Victory Point margin does not put two Attendees
     * on the same record into different groups.
     *
     * @param  Collection<int, EventAttendee>  $ranked
     * @param  array<int, array<int, float>>  $totals
     * @return array<int, int>
     */
    private function scoreGroups(Collection $ranked, ?EventScoreType $primary, array $totals): array
    {
        if (! $primary instanceof EventScoreType) {
            return $ranked->mapWithKeys(fn (EventAttendee $a): array => [$a->id => 0])->all();
        }

        $groups = [];
        $group = -1;
        $previousTotal = null;

        foreach ($ranked as $attendee) {
            $total = $totals[$attendee->id][$primary->id] ?? 0.0;

            if ($previousTotal === null || $total !== $previousTotal) {
                $group++;
                $previousTotal = $total;
            }

            $groups[$attendee->id] = $group;
        }

        return $groups;
    }

    /**
     * Who each Attendee has already met at this Event.
     *
     * @return array<int, array<int, true>>
     */
    private function previousOpponents(Event $event): array
    {
        $rows = DB::table('game_attendee as side')
            ->join('game_attendee as opponent', function ($join): void {
                $join->on('side.game_id', '=', 'opponent.game_id')
                    ->whereColumn('side.event_attendee_id', '!=', 'opponent.event_attendee_id');
            })
            ->join('games', 'games.id', '=', 'side.game_id')
            ->join('rounds', 'rounds.id', '=', 'games.round_id')
            ->where('rounds.event_id', $event->getKey())
            ->select('side.event_attendee_id as attendee_id', 'opponent.event_attendee_id as opponent_id')
            ->get();

        $met = [];

        foreach ($rows as $row) {
            $met[(int) $row->attendee_id][(int) $row->opponent_id] = true;
        }

        return $met;
    }

    /**
     * @return array<int, true>
     */
    private function attendeesGivenByes(Event $event): array
    {
        $attendeeIds = DB::table('game_attendee')
            ->join('games', 'games.id', '=', 'game_attendee.game_id')
            ->join('rounds', 'rounds.id', '=', 'games.round_id')
            ->where('rounds.event_id', $event->getKey())
            ->where('games.is_bye', true)
            ->pluck('game_attendee.event_attendee_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        return array_fill_keys($attendeeIds, true);
    }

    /**
     * Divide the field into the two sides of the matching, sitting out however
     * many Attendees the sides differ by.
     *
     * @param  Collection<int, EventAttendee>  $ranked
     * @param  array<int, true>  $alreadyGivenByes
     * @return array{Collection<int, EventAttendee>, Collection<int, EventAttendee>, list<EventAttendee>}
     */
    private function divide(Event $event, Collection $ranked, array $alreadyGivenByes): array
    {
        if ($event->settings->requiresOpposedAllegiance) {
            $loyalists = $ranked->where('allegiance', Allegiance::Loyalist)->values();
            $traitors = $ranked->where('allegiance', Allegiance::Traitor)->values();

            $majority = $loyalists->count() >= $traitors->count() ? $loyalists : $traitors;
            $byes = $this->chooseByes($majority, abs($loyalists->count() - $traitors->count()), $alreadyGivenByes);

            return [
                $this->withoutByes($loyalists, $byes),
                $this->withoutByes($traitors, $byes),
                $byes,
            ];
        }

        $byes = $this->chooseByes($ranked, $ranked->count() % 2, $alreadyGivenByes);

        $playing = $this->withoutByes($ranked, $byes);
        $half = intdiv($playing->count(), 2);

        return [$playing->take($half)->values(), $playing->skip($half)->values(), $byes];
    }

    /**
     * @param  Collection<int, EventAttendee>  $side
     * @param  list<EventAttendee>  $byes
     * @return Collection<int, EventAttendee>
     */
    private function withoutByes(Collection $side, array $byes): Collection
    {
        $sittingOut = array_column($byes, 'id');

        return $side
            ->reject(fn (EventAttendee $attendee): bool => in_array($attendee->id, $sittingOut, true))
            ->values();
    }

    /**
     * The Bye goes to the lowest-ranked eligible Attendee that has not already
     * had one. Once everyone eligible has had one somebody must still sit out,
     * so the rule relaxes to lowest-ranked rather than failing.
     *
     * @param  Collection<int, EventAttendee>  $candidates  in rank order
     * @param  array<int, true>  $alreadyGivenByes
     * @return list<EventAttendee>
     */
    private function chooseByes(Collection $candidates, int $count, array $alreadyGivenByes): array
    {
        if ($count <= 0) {
            return [];
        }

        $lowestRankedFirst = $candidates->reverse()->values();

        $neverHadOne = $lowestRankedFirst
            ->reject(fn (EventAttendee $attendee): bool => isset($alreadyGivenByes[$attendee->id]))
            ->take($count);

        $shortfall = $count - $neverHadOne->count();

        if ($shortfall <= 0) {
            return $neverHadOne->values()->all();
        }

        $repeats = $lowestRankedFirst
            ->reject(fn (EventAttendee $attendee): bool => $neverHadOne->contains('id', $attendee->id))
            ->take($shortfall);

        return $neverHadOne->concat($repeats)->values()->all();
    }

    /**
     * Score-group distance, squared so that the matcher spreads pair-downs
     * evenly rather than treating one four-group drop as two two-group drops,
     * plus a rematch penalty large enough to outweigh every possible
     * arrangement of the rest of the matrix.
     *
     * The penalty stays finite on purpose: rematches are then avoided whenever
     * any alternative exists, but a pairing still comes back when the field is
     * genuinely stuck, which is what the flagging story assumes.
     *
     * @param  Collection<int, EventAttendee>  $sideA
     * @param  Collection<int, EventAttendee>  $sideB
     * @param  array<int, int>  $scoreGroups
     * @param  array<int, array<int, true>>  $previousOpponents
     * @return array<int, array<int, int>>
     */
    private function costMatrix(
        Collection $sideA,
        Collection $sideB,
        array $scoreGroups,
        array $previousOpponents,
        int $fieldSize,
    ): array {
        $rematchPenalty = ($fieldSize ** 3) + 1;

        $matrix = [];

        foreach ($sideA as $row => $home) {
            foreach ($sideB as $column => $away) {
                $distance = ($scoreGroups[$home->id] ?? 0) - ($scoreGroups[$away->id] ?? 0);
                $rematch = isset($previousOpponents[$home->id][$away->id]);

                $matrix[$row][$column] = ($distance ** 2) + ($rematch ? $rematchPenalty : 0);
            }
        }

        return $matrix;
    }

    /**
     * Table one is the top of the field, so Games are numbered by the best rank
     * they contain. Byes hold no table.
     *
     * @param  Collection<int, EventAttendee>  $ranked
     * @param  list<array{EventAttendee, EventAttendee}>  $pairs
     * @param  list<EventAttendee>  $byes
     */
    private function persist(Event $event, int $roundNumber, Collection $ranked, array $pairs, array $byes): Round
    {
        $positionOf = [];

        foreach ($ranked as $position => $attendee) {
            $positionOf[$attendee->id] = $position;
        }

        usort($pairs, fn (array $a, array $b): int => min($positionOf[$a[0]->id], $positionOf[$a[1]->id])
            <=> min($positionOf[$b[0]->id], $positionOf[$b[1]->id]));

        return DB::transaction(function () use ($event, $roundNumber, $pairs, $byes): Round {
            $round = $event->rounds()->create([
                'number' => $roundNumber,
                'status' => RoundStatus::Draft,
            ]);

            foreach ($pairs as $index => [$home, $away]) {
                $game = $round->games()->create([
                    'table_number' => $index + 1,
                    'is_bye' => false,
                ]);

                $game->attendees()->attach([$home->id, $away->id]);
            }

            foreach ($byes as $attendee) {
                $game = $round->games()->create([
                    'table_number' => null,
                    'is_bye' => true,
                ]);

                $game->attendees()->attach($attendee->id);

                $this->storeGameScores->awardByeWin($game);
            }

            return $round;
        });
    }
}
