<?php

namespace App\Actions\Events;

use App\Enums\Allegiance;
use App\Exceptions\EventIsFull;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Registers a party for an Event.
 *
 * Every Player named is a member from the moment the Captain submits the form,
 * whether or not they have an account: the ones who do not are invited, which
 * creates the unclaimed account the membership hangs off. A team is therefore
 * complete and pairable the day it registers rather than the day its last
 * member reads their email.
 */
class RegisterAttendee
{
    public function __construct(private readonly EnrolPlayer $enrolPlayer) {}

    /**
     * @param  list<array{name?: string|null, email: string, faction_id?: int|null, army_list?: string|null}>  $players
     */
    public function handle(
        Event $event,
        array $players,
        User $registeredBy,
        ?string $name = null,
        ?Allegiance $allegiance = null,
    ): EventAttendee {
        return DB::transaction(function () use ($event, $players, $registeredBy, $name, $allegiance): EventAttendee {
            // Inside the transaction and counted fresh: the policy check that
            // let this request through happened before the last place may have
            // gone, and an over-full Event cannot be paired. Organisers are
            // exempt here as they are in the policy — squeezing in a straggler
            // on the day is part of running an event.
            if (! $event->isOrganisedBy($registeredBy) && $event->fresh()?->isFull() === true) {
                throw EventIsFull::for($event);
            }

            $attendee = $event->attendees()->create([
                'name' => $name,
                'allegiance' => $allegiance,
            ]);

            foreach ($players as $player) {
                $this->enrolPlayer->handle($attendee, $player, $registeredBy);
            }

            return $attendee;
        });
    }
}
