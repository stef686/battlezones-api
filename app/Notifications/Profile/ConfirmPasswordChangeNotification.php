<?php

namespace App\Notifications\Profile;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ConfirmPasswordChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute('password.change.confirm', now()->addDay(), [
            'user' => $notifiable->getKey(),
            'token' => $this->token,
        ]);

        return (new MailMessage())
            ->subject('Confirm Your Password Change')
            ->greeting('Hello!')
            ->line('You requested to change your password on your Battlezones account.')
            ->action('Confirm Password Change', $url)
            ->line('This link will expire in 24 hours.')
            ->line('If you did not request this change, please contact support immediately.')
            ->salutation('The Battlezones Team');
    }
}
