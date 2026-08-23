<?php

namespace App\Listeners\Events;

use App\Enums\ResultActivity;
use App\Events\ResultEdited;
use App\Notifications\Events\ResultActivityNotification;
use Illuminate\Support\Facades\Notification;

class NotifyResultEdited
{
    /**
     * Everyone in the Game, the original submitter included: their score has
     * been changed by somebody else, which is precisely what they need to know.
     */
    public function handle(ResultEdited $event): void
    {
        Notification::send($event->game->players()->get(), new ResultActivityNotification(
            $event->game,
            ResultActivity::Edited,
            $event->editedBy,
        ));
    }
}
