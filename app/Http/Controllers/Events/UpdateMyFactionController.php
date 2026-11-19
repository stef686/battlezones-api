<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateMyFactionRequest;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendeeMembership;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateMyFactionController extends Controller
{
    /**
     * The Faction locks with the army list: opponents prepare against both.
     * The write is conditional on the list still being unlocked, so a list
     * submitted at the same moment cannot have its Faction changed under it.
     */
    #[Endpoint('Record My Faction', 'The Faction this Player is bringing. Personal to the Player, not the party: a doubles team fields two Factions under one Allegiance. Refused once their army list is submitted, until an Organiser reopens it.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(status: 200, content: ['data' => [
        'id' => 9,
        'name' => 'Ada and Grace',
        'allegiance' => 'loyalist',
        'members' => [[
            'id' => 12,
            'name' => 'Ada Lovelace',
            'faction' => ['id' => 3, 'name' => 'Sons of Horus'],
            'army_list' => 'Legion Tactical Squad, 10 models...',
            'clubs' => [['id' => 2, 'name' => 'The Ordo Ludi']],
        ]],
        'checked_in_at' => null,
        'custom_field_responses' => [],
        'games' => [],
    ]])]
    #[Response(['message' => 'Your list is locked. Ask an organiser to reopen it if you need to change your Faction.'], 403, 'The Player\'s army list is locked.')]
    #[Response(['message' => 'Not Found.'], 404, 'This Player has not entered this Event.')]
    public function __invoke(UpdateMyFactionRequest $request, Event $event): EventAttendeeDetailResource
    {
        // The Player's own membership, not one addressed by id. There is
        // exactly one per Event, and an invited Player who has not claimed
        // their account yet cannot be named in a URL at all — an unclaimed
        // User refuses route binding — but must still be able to say what
        // they are bringing.
        $membership = EventAttendeeMembership::query()
            ->where('event_id', $event->getKey())
            ->where('user_id', $request->user()->getKey())
            ->firstOrFail();

        $changed = EventAttendeeMembership::query()
            ->whereKey($membership->getKey())
            ->whereNull('army_list_submitted_at')
            ->update(['faction_id' => $request->validated('faction_id')]);

        abort_if(
            $changed === 0,
            403,
            'Your list is locked. Ask an organiser to reopen it if you need to change your Faction.',
        );

        $attendee = $membership->attendee()->firstOrFail();

        $attendee->loadForDetail();

        return EventAttendeeDetailResource::make($attendee);
    }
}
