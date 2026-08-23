<?php

namespace App\Notifications\Events;

use App\Enums\NotificationType;
use App\Enums\ResultActivity;
use App\Models\Game;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Something happened to a Game's result: it was submitted, an Organiser
 * corrected it, someone flagged it, or a flag was resolved.
 */
class ResultActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Game $game,
        public ResultActivity $activity,
        public User $actor,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        /** @var User $notifiable */
        return $notifiable->getNotificationDrivers(NotificationType::ResultActivity);
    }

    /**
     * Ids and a type, never names: a stored Attendee name goes stale the
     * moment an Organiser fixes a typo, and the client is fetching the screen
     * anyway.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => NotificationType::ResultActivity->value,
            'activity' => $this->activity->value,
            'event_id' => $this->game->round->event_id,
            'round_id' => $this->game->round_id,
            'game_id' => $this->game->id,
            'actor_id' => $this->actor->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('There is an update on your game')
            ->line('There has been an update to a result at an event you are playing.')
            ->salutation('The Battlezones Team');
    }
}
