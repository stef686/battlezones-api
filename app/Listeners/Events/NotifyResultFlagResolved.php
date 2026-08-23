<?php

namespace App\Listeners\Events;

use App\Enums\ResultActivity;
use App\Events\ResultFlagResolved;
use App\Notifications\Events\ResultActivityNotification;

class NotifyResultFlagResolved
{
    /**
     * The Player who raised the flag: they are the one waiting on an answer.
     */
    public function handle(ResultFlagResolved $event): void
    {
        $event->flag->flaggedBy->notify(new ResultActivityNotification(
            $event->flag->game,
            ResultActivity::FlagResolved,
            $event->resolvedBy,
        ));
    }
}
