<?php

namespace App\Exceptions;

use App\Models\Event;
use RuntimeException;

/**
 * Every place at the Event has been taken.
 *
 * Raised inside the registration transaction rather than only checked before
 * it: two Captains submitting for the last place is an ordinary occurrence on
 * the morning entries open, and the check that decides it has to be the one
 * holding the row lock.
 */
class EventIsFull extends RuntimeException
{
    public static function for(Event $event): self
    {
        return new self("{$event->name} is full. Ask an organiser whether there is a waiting list.");
    }
}
