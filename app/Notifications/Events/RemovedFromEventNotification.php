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
 * is gone by the time a queued job would try to load it. Two things take a
 * place away, and each has its own line: the whole Attendee being Deleted,
 * or this one Player being taken out of it.
 */
class RemovedFromEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const TYPE = 'removed_from_event';

    public function __construct(
        public Event $event,
        public User $removedBy,
        public string $attendeeName,
        public bool $wholeAttendee,
    ) {}

    /**
     * The Attendee this Player was in has been Deleted.
     */
    public static function attendeeDeleted(Event $event, User $removedBy, string $attendeeName): self
    {
        return new self($event, $removedBy, $attendeeName, wholeAttendee: true);
    }

    /**
     * This Player alone has been taken out of their Attendee.
     */
    public static function memberRemoved(Event $event, User $removedBy, string $attendeeName): self
    {
        return new self($event, $removedBy, $attendeeName, wholeAttendee: false);
    }

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
            ->line($this->wholeAttendee
                ? "{$this->remover()} has removed {$this->attendeeName} from {$this->event->name}. If you weren't expecting this, contact the organisers."
                : "{$this->remover()} has removed you from {$this->attendeeName} in {$this->event->name}.")
            ->action('View the event', Frontend::eventUrl($this->event))
            ->salutation('The Battlezones Team');
    }

    /**
     * Named when the Player knows them: an Organiser of the Event, or for a
     * member removal the team mate who did it. Anyone else is a platform admin
     * working Filament, whom the Players do not know, so "An organiser".
     */
    private function remover(): string
    {
        $known = $this->event->isOrganisedBy($this->removedBy)
            || (! $this->wholeAttendee && $this->event->isAttendedBy($this->removedBy));

        return $known ? $this->removedBy->public_name : 'An organiser';
    }
}
