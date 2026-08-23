<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventAttendeeMembership;
use App\Models\FeedbackInvitation;
use App\Models\User;
use App\Notifications\Events\FeedbackRequestNotification;
use Illuminate\Support\Str;

/**
 * Send every Player in an Event their own feedback link.
 */
class SendFeedbackRequests
{
    /**
     * Players who have already answered keep their submission and are left
     * alone; everybody else gets a fresh token, so a resend a fortnight later
     * is not delivering a link that has since expired.
     *
     * @return int the number of Players invited
     */
    public function execute(Event $event): int
    {
        $players = User::query()
            ->whereIn('id', EventAttendeeMembership::query()
                ->where('event_id', $event->getKey())
                ->select('user_id'))
            ->get();

        $invited = 0;

        foreach ($players as $player) {
            $invitation = FeedbackInvitation::query()->firstOrNew([
                'event_id' => $event->getKey(),
                'user_id' => $player->getKey(),
            ]);

            if ($invitation->submitted_at !== null) {
                continue;
            }

            $plainToken = Str::random(64);

            $invitation->fill([
                'token' => FeedbackInvitation::hashToken($plainToken),
                'sent_at' => now(),
                'expires_at' => now()->addDays(FeedbackInvitation::LIFETIME_DAYS),
            ])->save();

            $player->notify(new FeedbackRequestNotification($event, $plainToken));

            $invited++;
        }

        return $invited;
    }
}
