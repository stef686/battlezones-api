<?php

namespace App\Notifications\Events;

use App\Enums\NotificationType;
use App\Models\Game;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A Round has been published: this Player's pairing and table are up.
 */
class RoundLiveNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Game $game) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        /** @var User $notifiable */
        return $notifiable->getNotificationDrivers(NotificationType::RoundLive);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => NotificationType::RoundLive->value,
            'event_id' => $this->game->round->event_id,
            'round_id' => $this->game->round_id,
            'game_id' => $this->game->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your next game is up')
            ->line('The next round has been published. Your pairing and table are ready.')
            ->salutation('The Battlezones Team');
    }
}
