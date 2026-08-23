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
 */
class EventStandingsQuery
{
    /**
     * @var Collection<int, EventScoreType>
     */
    private Collection $scoreTypes;

    private ?string $search = null;

    private ?EventScoreType $sortBy = null;

    private function __construct(private Event $event)
    {
        $this->scoreTypes = $event->scoreTypes()->orderBy('display_order')->get();
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
    private function rankedTotals(): QueryBuilder
    {
        $ranking = $this->scoreTypes
            ->whereNotNull('ranking_order')
            ->sortBy('ranking_order')
            ->map(fn (EventScoreType $scoreType): string => "{$this->column($scoreType)} {$scoreType->sort_direction->value}")
            ->values();

        $order = $ranking->isEmpty() ? 'id' : $ranking->implode(', ');

        return DB::query()
            ->fromSub($this->attendeeTotals(), 'totals')
            ->select('totals.*')
            ->selectRaw("RANK() OVER (ORDER BY {$order}) as position");
    }

    private function attendeeTotals(): QueryBuilder
    {
        $totals = DB::table('event_attendees')
            ->leftJoin('game_scores', 'game_scores.event_attendee_id', '=', 'event_attendees.id')
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

        return new Standing(
            position: (int) $attendee->getAttribute('position'),
            attendee: $attendee,
            scores: $scores,
        );
    }

    private function column(EventScoreType $scoreType): string
    {
        return "score_{$scoreType->getKey()}";
    }
}
