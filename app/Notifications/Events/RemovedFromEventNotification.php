<?php

namespace App\Notifications\Events;

use App\Models\Event;
use App\Models\EventAttendee;
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

    private const UNNAMED_REMOVER = 'An organiser';

    /**
     * Built through attendeeDeleted() or memberRemoved(), which resolve the
     * names at the moment of removal: by the time a queued job renders the
     * mail the remover may have left the team or the Event.
     */
    private function __construct(
        public Event $event,
        public User $removedBy,
        public string $removerName,
        public string $attendeeName,
        public bool $wholeAttendee,
    ) {}

    /**
     * The Attendee this Player was in has been Deleted.
     *
     * Only its Organisers and platform admins can delete one, so the remover
     * is named when they organise the Event; a platform admin, whom the
     * Players do not know, reads as "An organiser".
     */
    public static function attendeeDeleted(Event $event, User $removedBy, string $attendeeName): self
    {
        $removerName = $event->isOrganisedBy($removedBy) ? $removedBy->public_name : self::UNNAMED_REMOVER;

        return new self($event, $removedBy, $removerName, $attendeeName, wholeAttendee: true);
    }

    /**
     * This Player alone has been taken out of their Attendee, which is read
     * as it stands after the removal.
     *
     * The remover is named when the Player knows them: an Organiser of the
     * Event, or a team mate still in this Attendee. Anyone else is a platform
     * admin, who reads as "An organiser".
     */
    public static function memberRemoved(EventAttendee $attendee, User $removedBy): self
    {
        $event = $attendee->event;
        $known = $event->isOrganisedBy($removedBy) || $attendee->hasMember($removedBy);

        return new self(
            $event,
            $removedBy,
            $known ? $removedBy->public_name : self::UNNAMED_REMOVER,
            $attendee->displayName(),
            wholeAttendee: false,
        );
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
                ? "{$this->removerName} has removed {$this->attendeeName} from {$this->event->name}. If you weren't expecting this, contact the organisers."
                : "{$this->removerName} has removed you from {$this->attendeeName} in {$this->event->name}.")
            ->action('View the event', Frontend::eventUrl($this->event))
            ->salutation('The Battlezones Team');
    }
}
