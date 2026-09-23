<?php

namespace App\Enums;

/**
 * Where the thing a schedule block describes has got to.
 *
 * Derived on every read rather than stored on the Round: `live` is already a
 * latch and the Event knows which Round is its current one, so a stored
 * "finished" would be a second copy of that fact — one somebody has to set,
 * and unset again when an Organiser withdraws a Round.
 */
enum ScheduleTargetState: string
{
    /** Happening now: the current Round, or a Poll that is open. */
    case Live = 'live';

    /** Done: a Round the field has moved past, or a Poll that has closed. */
    case Finished = 'finished';
}
