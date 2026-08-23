<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A Game's result has been recorded by one of its Players.
 *
 * Nothing listens yet: broadcasting and notifications hang off this rather
 * than being plumbed back into the write path later.
 */
class ResultSubmitted
{
    use Dispatchable;

    public function __construct(
        public Game $game,
        public User $submittedBy,
    ) {}
}
