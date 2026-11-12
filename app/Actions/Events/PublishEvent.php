<?php

namespace App\Actions\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Validation\ValidationException;

/**
 * Take a Draft Event to Published, so Players can find and enter it.
 *
 * Its own transition rather than a field on the Event PATCH (ADR 0002).
 * Every unmet precondition is refused at once, each under the field that
 * fixes it, so an Organiser learns everything that stands in the way from
 * one attempt. The organise hub's checklist (`publishChecklist` in
 * `frontend/src/lib/publishing.ts`) mirrors these; change both together.
 */
class PublishEvent
{
    /**
     * @throws ValidationException
     */
    public function execute(Event $event): void
    {
        if ($event->status !== EventStatus::Draft) {
            throw ValidationException::withMessages(['status' => 'Only a draft event can be published.']);
        }

        $unmet = array_filter([
            'venue_name' => blank($event->venue_name) ? 'Name the venue before publishing.' : null,
            'venue_city' => blank($event->venue_city) ? 'Say which town or city the venue is in before publishing.' : null,
            'ends_at' => $event->ends_at->isPast() ? 'This event has already ended, so it cannot be published.' : null,
        ]);

        if ($unmet !== []) {
            throw ValidationException::withMessages($unmet);
        }

        $event->update(['status' => EventStatus::Published]);
    }
}
