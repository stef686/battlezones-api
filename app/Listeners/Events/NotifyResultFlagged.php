<?php

namespace App\Listeners\Events;

use App\Enums\ResultActivity;
use App\Events\ResultFlagged;
use App\Notifications\Events\ResultActivityNotification;
use Illuminate\Support\Facades\Notification;

class NotifyResultFlagged
{
    /**
     * Every Organiser of the Event, not only the lead: whoever is nearest the
     * table is the one who can settle it.
     */
    public function handle(ResultFlagged $event): void
    {
        $game = $event->flag->game;

        Notification::send($game->round->event->organisers, new ResultActivityNotification(
            $game,
            ResultActivity::FlagRaised,
            $event->flag->flaggedBy,
        ));
    }
}
