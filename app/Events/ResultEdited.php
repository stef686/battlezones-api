<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An Organiser corrected a Game's result after the fact.
 */
class ResultEdited
{
    use Dispatchable;

    public function __construct(
        public Game $game,
        public User $editedBy,
    ) {}
}
