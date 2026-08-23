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
     */
    public function __construct(
        public int $position,
        public EventAttendee $attendee,
        public Collection $scores,
    ) {}
}
