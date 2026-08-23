<?php

namespace App\Listeners\Events;

use App\Actions\Events\OpenFavouriteOpponentVoting;
use App\Events\ResultEdited;

class OpenVotingOnResultEdited
{
    public function __construct(private OpenFavouriteOpponentVoting $openVoting) {}

    /**
     * An Organiser correcting the last outstanding result completes that team
     * just as a Player submission would.
     */
    public function handle(ResultEdited $event): void
    {
        $this->openVoting->forGame($event->game->load('attendees.memberships', 'round.event'));
    }
}
