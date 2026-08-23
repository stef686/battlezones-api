<?php

namespace App\Listeners\Events;

use App\Actions\Events\OpenFavouriteOpponentVoting;
use App\Events\ResultSubmitted;

class OpenVotingOnResultSubmitted
{
    public function __construct(private OpenFavouriteOpponentVoting $openVoting) {}

    public function handle(ResultSubmitted $event): void
    {
        $this->openVoting->forGame($event->game->load('attendees.memberships', 'round.event'));
    }
}
