<?php

namespace App\Listeners\Events;

use App\Enums\ResultActivity;
use App\Events\ResultSubmitted;
use App\Notifications\Events\ResultActivityNotification;
use Illuminate\Support\Facades\Notification;

class NotifyResultSubmitted
{
    /**
     * Everyone in the Game except the submitter — including their own partner,
     * since either Player of an Attendee can submit.
     */
    public function handle(ResultSubmitted $event): void
    {
        $players = $event->game->players()
            ->whereKeyNot($event->submittedBy->getKey())
            ->get();

        Notification::send($players, new ResultActivityNotification(
            $event->game,
            ResultActivity::Submitted,
            $event->submittedBy,
        ));
    }
}
