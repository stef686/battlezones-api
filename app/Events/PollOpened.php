<?php

namespace App\Events;

use App\Models\EventPoll;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An Organiser opened a Poll's voting window.
 */
class PollOpened
{
    use Dispatchable;

    public function __construct(public EventPoll $poll) {}
}
