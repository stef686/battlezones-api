<?php

namespace App\Listeners\Events;

use App\Events\PollOpened;
use App\Models\EventAttendeeMembership;
use App\Models\User;
use App\Notifications\Events\VotingOpenNotification;
use Illuminate\Support\Facades\Notification;

class NotifyVotingOpened
{
    /**
     * Everybody playing the Event: voting closes while people are still in the
     * hall, so the window is short and missing it is the failure mode.
     */
    public function handle(PollOpened $event): void
    {
        User::query()
            ->whereIn('id', EventAttendeeMembership::query()
                ->where('event_id', $event->poll->event_id)
                ->select('user_id'))
            ->chunkById(100, function ($players) use ($event): void {
                Notification::send($players, new VotingOpenNotification($event->poll));
            });
    }
}
