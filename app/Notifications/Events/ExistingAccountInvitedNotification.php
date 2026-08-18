<?php

namespace App\Notifications\Events;

use App\Enums\EventInviteRole;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent instead of an Invite when the invited email already has an account.
 *
 * Someone else has enrolled this person on their say-so, so they are told
 * rather than quietly signed up, and they enter with their own login rather
 * than an emailed credential.
 */
class ExistingAccountInvitedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event,
        public EventInviteRole $role,
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
        return (new MailMessage())
            ->subject("You have been added to {$this->event->name}")
            ->greeting('Hello!')
            ->line($this->invitationLine())
            ->line('Log in with your Battlezones account to take part. There is no new account to set up.')
            ->line('If you were not expecting this, you can safely ignore it — nothing has changed on your account.')
            ->salutation('The Battlezones Team');
    }

    private function invitationLine(): string
    {
        return match ($this->role) {
            EventInviteRole::Captain => "You have been invited to enter {$this->event->name}.",
            EventInviteRole::Player => "You have been invited to join a team at {$this->event->name}.",
        };
    }
}
