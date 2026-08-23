<?php

namespace App\Notifications\Auth;

use App\Services\Frontend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
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
        $resetUrl = Frontend::resetPasswordUrl($this->token, $notifiable->getEmailForPasswordReset());

        return (new MailMessage())
            ->subject('Reset Your Battlezones Password')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your Battlezones account.')
            ->action('Reset Password', $resetUrl)
            ->line('This password reset link will expire in 7 days.')
            ->line('If you did not request a password reset, no further action is required — your account remains secure.')
            ->salutation('The Battlezones Team');
    }
}
