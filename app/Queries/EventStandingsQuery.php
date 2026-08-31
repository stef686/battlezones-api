<?php

namespace App\Queries;

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Standings computed from Games and their scores.
 *
 * Every Attendee appears, whether or not they have played, and Attendees on
 * equal scores share a position.
 *
 * Movement between Rounds is computed the same way rather than snapshotted:
 * a score belongs to a Game, a Game to a Round, so "where everybody stood
 * after Round 2" is this same aggregate with the later Rounds left out. A
 * stored position would have to be rewritten by every path that touches a
 * score — an Organiser's edit, a bye, a flag resolution — and the one that
 * forgets leaves an arrow pointing the wrong way for the rest of the Event.
 */
class EventStandingsQuery
{
    /**
     * @var Collection<int, EventScoreType>
     */
    private Collection $scoreTypes;

    private ?string $search = null;

    private ?EventScoreType $sortBy = null;

    /**
     * The last Round anybody had finished a Game in before the one being
     * played, or null where this is the first — there is nowhere to have
     * moved from until two Rounds have results in them.
     */
    private ?int $previousRoundNumber;

    private function __construct(private Event $event)
    {
        $this->scoreTypes = $event->scoreTypes()->orderBy('display_order')->get();
        $this->previousRoundNumber = $this->roundBeforeTheLatestScoredOne();
    }

    public static function forEvent(Event $event): self
    {
        return new self($event);
    }

    public function search(?string $term): self
    {
        $this->search = $term;

        return $this;
    }

    public function sortBy(?EventScoreType $scoreType): self
    {
        $this->sortBy = $scoreType;

        return $this;
    }

    /**
     * @return LengthAwarePaginator<int, Standing>
     */
    public function paginate(): LengthAwarePaginator
    {
        return $this->toQuery()->paginate()->through(fn (EventAttendee $attendee): Standing => $this->toStanding($attendee));
    }

    /**
     * @return Builder<EventAttendee>
     */
    private function toQuery(): Builder
    {
        $query = EventAttendee::query()
            ->joinSub($this->rankedTotals(), 'standings', 'standings.id', '=', 'event_attendees.id')
            ->where('event_attendees.event_id', $this->event->getKey())
            ->with(['memberships.user.clubs', 'memberships.faction'])
            ->select([
                'event_attendees.*',
                'standings.position',
                ...$this->scoreTypes->map(fn (EventScoreType $scoreType): string => "standings.{$this->column($scoreType)}")->all(),
            ]);

        if ($this->previousRoundNumber !== null) {
            $query->joinSub(
                $this->rankedTotals($this->previousRoundNumber),
                'previous_standings',
                'previous_standings.id',
                '=',
                'event_attendees.id',
            )->addSelect('previous_standings.position as previous_position');
        }

        $this->applySearch($query);

        return $this->sortBy instanceof EventScoreType
            ? $query->orderBy("standings.{$this->column($this->sortBy)}", $this->sortBy->sort_direction->value)
                ->orderBy('event_attendees.id')
            : $query->orderBy('standings.position')->orderBy('event_attendees.id');
    }

    /**
     * Attendee totals with a shared position for equal scores.
     *
     * Ranking follows `ranking_order` — Match Points before Victory Points —
     * so RANK() gives tied Attendees the same position.
     */
    private function rankedTotals(?int $throughRoundNumber = null): QueryBuilder
    {
        $ranking = $this->scoreTypes
            ->whereNotNull('ranking_order')
            ->sortBy('ranking_order')
            ->map(fn (EventScoreType $scoreType): string => "{$this->column($scoreType)} {$scoreType->sort_direction->value}")
            ->values();

        $order = $ranking->isEmpty() ? 'id' : $ranking->implode(', ');

        return DB::query()
            ->fromSub($this->attendeeTotals($throughRoundNumber), 'totals')
            ->select('totals.*')
            ->selectRaw("RANK() OVER (ORDER BY {$order}) as position");
    }

    /**
     * Every Attendee's totals, optionally as they stood at the end of a Round.
     *
     * The Round cap is applied to the join rather than as a `where`, so an
     * Attendee who had played nothing by then still appears, on zero, instead
     * of dropping out of the ranking and reading as having climbed into it.
     */
    private function attendeeTotals(?int $throughRoundNumber = null): QueryBuilder
    {
        $totals = DB::table('event_attendees')
            ->leftJoin('game_scores', function ($join) use ($throughRoundNumber): void {
                $join->on('game_scores.event_attendee_id', '=', 'event_attendees.id');

                if ($throughRoundNumber !== null) {
                    $join->whereIn('game_scores.game_id', $this->gameIdsThroughRound($throughRoundNumber));
                }
            })
            ->where('event_attendees.event_id', $this->event->getKey())
            ->groupBy('event_attendees.id')
            ->select('event_attendees.id');

        foreach ($this->scoreTypes as $scoreType) {
            $totals->selectRaw(
                'coalesce(sum(case when game_scores.event_score_type_id = ? then game_scores.value end), 0) as '.$this->column($scoreType),
                [$scoreType->getKey()],
            );
        }

        return $totals;
    }

    /**
     * The Games played up to and including a Round.
     */
    private function gameIdsThroughRound(int $number): QueryBuilder
    {
        return DB::table('games')
            ->join('rounds', 'rounds.id', '=', 'games.round_id')
            ->where('rounds.event_id', $this->event->getKey())
            ->where('rounds.number', '<=', $number)
            ->select('games.id');
    }

    /**
     * The Round the movement is measured from: the one before the latest that
     * anybody has finished a Game in.
     *
     * Read from scores rather than from a status, because a Round has no
     * completed state to read and a stored one can disagree with the results
     * it claims to summarise. Measured from the Round before the latest scored
     * one rather than from that one, so an Event mid-Round shows how it stood
     * going into the Round being played rather than an arrow against itself.
     */
    private function roundBeforeTheLatestScoredOne(): ?int
    {
        $scored = DB::table('rounds')
            ->join('games', 'games.round_id', '=', 'rounds.id')
            ->join('game_scores', 'game_scores.game_id', '=', 'games.id')
            ->where('rounds.event_id', $this->event->getKey())
            ->distinct()
            ->orderByDesc('rounds.number')
            ->pluck('rounds.number');

        return $scored->count() < 2 ? null : (int) $scored[1];
    }

    /**
     * @param  Builder<EventAttendee>  $query
     */
    private function applySearch(Builder $query): void
    {
        if (blank($this->search)) {
            return;
        }

        $term = '%'.$this->search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->where('event_attendees.name', 'like', $term)
                ->orWhereHas('memberships.user', fn (Builder $query) => $query->where('users.name', 'like', $term))
                ->orWhereHas('memberships.faction', fn (Builder $query) => $query->where('factions.name', 'like', $term))
                ->orWhereHas('memberships.user.clubs', fn (Builder $query) => $query->where('clubs.name', 'like', $term));
        });
    }

    private function toStanding(EventAttendee $attendee): Standing
    {
        $scores = $this->scoreTypes->map(fn (EventScoreType $scoreType): array => [
            'value' => number_format((float) $attendee->getAttribute($this->column($scoreType)), 2, '.', ''),
            'scoreType' => $scoreType,
        ])->values();

        $previous = $attendee->getAttribute('previous_position');

        return new Standing(
            position: (int) $attendee->getAttribute('position'),
            attendee: $attendee,
            scores: $scores,
            previousPosition: $previous === null ? null : (int) $previous,
        );
    }

    private function column(EventScoreType $scoreType): string
    {
        return "score_{$scoreType->getKey()}";
    }
}
