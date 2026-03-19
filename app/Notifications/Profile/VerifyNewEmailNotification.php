<?php

namespace App\Notifications\Profile;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyNewEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $token,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::signedRoute('email.change.verify', [
            'user' => $this->user->id,
            'token' => $this->token,
        ]);

        return (new MailMessage())
            ->subject('Verify Your New Email Address')
            ->greeting('Hello!')
            ->line('You requested to change your email address on your Battlezones account.')
            ->action('Verify New Email', $url)
            ->line('If you did not request this change, no further action is required.')
            ->salutation('The Battlezones Team');
    }
}
