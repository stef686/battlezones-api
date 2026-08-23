<?php

namespace App\Notifications\Events;

use App\Enums\NotificationType;
use App\Models\EventPoll;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A Poll's voting window has opened.
 */
class VotingOpenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EventPoll $poll) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        /** @var User $notifiable */
        return $notifiable->getNotificationDrivers(NotificationType::VotingOpen);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => NotificationType::VotingOpen->value,
            'event_id' => $this->poll->event_id,
            'poll_id' => $this->poll->id,
            'poll_type' => $this->poll->type->value,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Voting is open')
            ->line('Voting has opened at an event you are playing.')
            ->salutation('The Battlezones Team');
    }
}
