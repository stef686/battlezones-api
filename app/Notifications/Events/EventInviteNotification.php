<?php

namespace App\Notifications\Events;

use App\Enums\EventInviteRole;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The email carrying an Invite's one and only plain token.
 *
 * Transactional: it delivers a credential without which the recipient cannot
 * reach the Event at all, so it is never gated by notification preferences.
 */
class EventInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event,
        public EventInviteRole $role,
        #[\SensitiveParameter] public string $plainToken,
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
        $url = url(route('invites.show', ['token' => $this->plainToken], false));

        $message = (new MailMessage())
            ->subject("You have been invited to {$this->event->name}")
            ->greeting('Hello!')
            ->line($this->invitationLine());

        return $message
            ->action('Open Your Invitation', $url)
            ->line('This link works until shortly after the event ends. Set a password when you follow it and the account is yours to keep.')
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
