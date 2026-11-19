<?php

namespace App\Notifications\Events;

use App\Models\Event;
use App\Models\User;
use App\Services\Frontend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Somebody else has taken this Player's place in an Event away from them.
 *
 * Transactional, like invites: email goes to every Player, claimed or not,
 * because an unclaimed Player's Invite went with their Attendee and email is
 * the only way left to reach them. A claimed Player gets an in-app entry too.
 * There is no NotificationType for it, so it never shows as a preference.
 *
 * It carries the Attendee's name as it stood rather than the Attendee, which
 * is gone by the time a queued job would try to load it.
 */
class RemovedFromEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const TYPE = 'removed_from_event';

    public function __construct(
        public Event $event,
        public User $removedBy,
        public string $attendeeName,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        /** @var User $notifiable */
        return $notifiable->isClaimed() ? ['mail', 'database'] : ['mail'];
    }

    /**
     * @return array{type: string, event_id: int, actor_id: int}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => self::TYPE,
            'event_id' => $this->event->id,
            'actor_id' => $this->removedBy->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("You're no longer entered in {$this->event->name}")
            ->line("{$this->remover()} has removed {$this->attendeeName} from {$this->event->name}. If you weren't expecting this, contact the organisers.")
            ->action('View the event', Frontend::eventUrl($this->event))
            ->salutation('The Battlezones Team');
    }

    /**
     * The Organiser by name, or "An organiser" for a platform admin working
     * Filament: the Players do not know them, and the email points them at the
     * organisers anyway.
     */
    private function remover(): string
    {
        return $this->event->isOrganisedBy($this->removedBy)
            ? $this->removedBy->public_name
            : 'An organiser';
    }
}
