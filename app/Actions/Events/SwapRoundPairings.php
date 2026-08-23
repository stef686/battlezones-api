<?php

namespace App\Actions\Events;

use App\Enums\Allegiance;
use App\Exceptions\CannotSwapPairing;
use App\Models\EventAttendee;
use App\Models\Game;
use App\Models\Round;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Exchange the same-allegiance side between two Games of a Draft Round.
 *
 * Under an opposed-allegiance Event, `L1 v T1` and `L2 v T2` can be recombined
 * two ways: exchanging the Traitor side gives `L1 v T2` and `L2 v T1` and stays
 * legal, while exchanging one whole team for another gives `L1 v L2` and
 * `T1 v T2` and does not. Only the first is a swap, so the Organiser picks two
 * Games and the exchange itself is not theirs to choose.
 *
 * Table numbers stay with the Game, so a swap never reshuffles the table list
 * and invalidates printed sheets.
 */
class SwapRoundPairings
{
    public function execute(Round $round, Game $first, Game $second): void
    {
        $this->guard($round, $first, $second);

        $first->load('attendees');
        $second->load('attendees');

        if ($first->is_bye || $second->is_bye) {
            [$bye, $game] = $first->is_bye ? [$first, $second] : [$second, $first];

            $this->moveBye($round, $bye, $game);

            return;
        }

        $this->exchangeSides($first, $second);
    }

    private function guard(Round $round, Game $first, Game $second): void
    {
        if (! $round->isDraft()) {
            throw CannotSwapPairing::roundNotDraft();
        }

        if ($first->is($second)) {
            throw CannotSwapPairing::sameGame();
        }

        if ($first->round_id !== $round->getKey() || $second->round_id !== $round->getKey()) {
            throw CannotSwapPairing::gameNotInRound();
        }

        if ($first->is_bye && $second->is_bye) {
            throw CannotSwapPairing::bothByes();
        }
    }

    /**
     * Exchange one side of each Game for the other's.
     *
     * Where the Event opposes Allegiances the exchanged side is the one both
     * Games share, which is what keeps every Game opposed. Where it does not,
     * the sides are indistinguishable, so the second Attendee of each Game is
     * exchanged and swapping the same two Games again undoes it.
     */
    private function exchangeSides(Game $first, Game $second): void
    {
        $firstSide = $this->sideToExchange($first);
        $secondSide = $this->matchingSide($second, $firstSide);

        DB::transaction(function () use ($first, $second, $firstSide, $secondSide): void {
            $first->attendees()->detach($firstSide->getKey());
            $second->attendees()->detach($secondSide->getKey());

            $first->attendees()->attach($secondSide->getKey());
            $second->attendees()->attach($firstSide->getKey());
        });
    }

    /**
     * The side that moves: the second Attendee of the Game.
     *
     * Both Games keep their first Attendee, so `L1 v T1` and `L2 v T2` become
     * `L1 v T2` and `L2 v T1` — the Attendee already sitting at that table
     * stays at it, and only their opponent changes.
     */
    private function sideToExchange(Game $game): EventAttendee
    {
        $side = $this->attendeesInPivotOrder($game)->last();

        if (! $side instanceof EventAttendee) {
            throw CannotSwapPairing::noOpposedSide();
        }

        return $side;
    }

    private function matchingSide(Game $game, EventAttendee $side): EventAttendee
    {
        if (! $game->round->event->settings->requiresOpposedAllegiance) {
            return $this->attendeesInPivotOrder($game)->last();
        }

        $match = $this->attendeesInPivotOrder($game)
            ->first(fn (EventAttendee $attendee): bool => $attendee->allegiance === $side->allegiance);

        if (! $match instanceof EventAttendee) {
            throw CannotSwapPairing::noOpposedSide();
        }

        return $match;
    }

    /**
     * Move the Bye rather than pairing against it.
     *
     * The bye Attendee joins the Game and the Attendee they displace — the one
     * on their own Allegiance, so the Game stays opposed — takes the Bye.
     */
    private function moveBye(Round $round, Game $bye, Game $game): void
    {
        $byeAttendee = $this->attendeesInPivotOrder($bye)->first();

        if (! $byeAttendee instanceof EventAttendee) {
            throw CannotSwapPairing::noOpposedSide();
        }

        $displaced = $round->event->settings->requiresOpposedAllegiance
            ? $this->matchingSide($game, $byeAttendee)
            : $this->attendeesInPivotOrder($game)->last();

        $this->guardMajorityAllegiance($round, $displaced);

        DB::transaction(function () use ($bye, $game, $byeAttendee, $displaced): void {
            $bye->attendees()->detach($byeAttendee->getKey());
            $game->attendees()->detach($displaced->getKey());

            $game->attendees()->attach($byeAttendee->getKey());
            $bye->attendees()->attach($displaced->getKey());
        });
    }

    /**
     * A Bye on the smaller Allegiance leaves the Round unpairable: every
     * remaining Attendee on the larger side needs an opponent that no longer
     * exists.
     */
    private function guardMajorityAllegiance(Round $round, EventAttendee $incomingBye): void
    {
        $event = $round->event;

        if (! $event->settings->requiresOpposedAllegiance) {
            return;
        }

        $loyalists = $event->attendees()->where('allegiance', Allegiance::Loyalist)->count();
        $traitors = $event->attendees()->where('allegiance', Allegiance::Traitor)->count();

        if ($loyalists === $traitors) {
            return;
        }

        $majority = $loyalists > $traitors ? Allegiance::Loyalist : Allegiance::Traitor;

        if ($incomingBye->allegiance !== $majority) {
            throw CannotSwapPairing::byeWouldLeaveMajority();
        }
    }

    /**
     * @return Collection<int, EventAttendee>
     */
    private function attendeesInPivotOrder(Game $game): Collection
    {
        return $game->attendees()->orderBy('game_attendee.id')->get();
    }
}
