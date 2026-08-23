<?php

namespace App\Enums;

/**
 * What happened to a Game's result.
 *
 * One notification type carries all four: a preference screen with a row per
 * event is one nobody reads.
 */
enum ResultActivity: string
{
    case Submitted = 'submitted';
    case Edited = 'edited';
    case FlagRaised = 'flag_raised';
    case FlagResolved = 'flag_resolved';
}
