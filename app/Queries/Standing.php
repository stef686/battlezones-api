<?php

namespace App\Queries;

use App\Models\EventAttendee;
use App\Models\EventScoreType;
use Illuminate\Support\Collection;

/**
 * An Attendee's ranked position in an Event with its accumulated totals.
 *
 * Computed on read rather than stored: a materialised standing has to be
 * recalculated on every path that touches a score, and missing one leaves
 * Standings quietly wrong while everyone is looking at them.
 */
class Standing
{
    /**
     * @param  Collection<int, array{value: string, scoreType: EventScoreType}>  $scores
     * @param  int|null  $previousPosition  Where this Attendee stood going into the Round being played, or null before there are two scored Rounds to compare.
     */
    public function __construct(
        public int $position,
        public EventAttendee $attendee,
        public Collection $scores,
        public ?int $previousPosition = null,
    ) {}

    /**
     * Places gained since the previous Round: positive for a climb, negative
     * for a drop, zero for holding, and null where there is nothing to compare
     * against yet.
     *
     * Worked out here rather than left to the client, which would otherwise
     * have to know that a smaller position is a better one.
     */
    public function movement(): ?int
    {
        return $this->previousPosition === null ? null : $this->previousPosition - $this->position;
    }
}
