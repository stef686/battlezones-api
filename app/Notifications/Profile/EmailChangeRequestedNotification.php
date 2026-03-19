<?php

namespace App\Notifications\Profile;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $newEmail) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Email Change Requested')
            ->greeting('Hello!')
            ->line('We received a request to change the email address associated with your Battlezones account.')
            ->line('If you did not request this change, please contact support immediately.')
            ->salutation('The Battlezones Team');
    }
}
