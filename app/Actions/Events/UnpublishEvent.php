<?php

namespace App\Actions\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Take a Published Event back to Draft, undoing a publish made too early.
 *
 * Only while nobody has entered and no Invite is outstanding. A Draft takes
 * nobody in, and an outstanding Invite's link would 404 the moment the Event
 * stopped being visible, so the line is the first way in rather than the
 * status: the same line ADR 0002 draws for reshaping an Event. Once anybody
 * is in, cancelling is the way out.
 *
 * The write is conditional on the row still being Published with nobody in
 * it, which is the whole race guard: an entry or an Invite landing between
 * the check and the write leaves nothing to update, and the loser is told
 * why.
 */
class UnpublishEvent
{
    /**
     * Whether this Event could go back to Draft right now.
     *
     * The single Event's viewer block asks this, so the Settings screen offers
     * the control only where the endpoint would accept it.
     */
    public function allows(Event $event): bool
    {
        return $event->status === EventStatus::Published && ! $this->hasAnyoneIn($event);
    }

    /**
     * @throws ValidationException
     */
    public function execute(Event $event): void
    {
        if ($event->status !== EventStatus::Published) {
            throw self::notPublished();
        }

        if ($this->hasAnyoneIn($event)) {
            throw self::someoneIsIn();
        }

        $unpublished = Event::query()
            ->whereKey($event->getKey())
            ->where('status', EventStatus::Published)
            ->whereDoesntHave('attendees')
            ->whereDoesntHave('invites', fn (Builder $invites) => $invites->outstanding())
            ->update(['status' => EventStatus::Draft]);

        if ($unpublished === 0) {
            $event->refresh();

            throw $event->status === EventStatus::Published ? self::someoneIsIn() : self::notPublished();
        }

        $event->refresh();
    }

    private function hasAnyoneIn(Event $event): bool
    {
        return $event->attendees()->exists()
            || $event->invites()->outstanding()->exists();
    }

    private static function notPublished(): ValidationException
    {
        return ValidationException::withMessages(['status' => 'Only a published event can be taken back to draft.']);
    }

    private static function someoneIsIn(): ValidationException
    {
        return ValidationException::withMessages(['status' => 'People have entered or been invited — cancel the event instead.']);
    }
}
