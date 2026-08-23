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
    #[Endpoint('Record My Faction', 'The Faction this Player is bringing. Personal to the Player, not the party: a doubles team fields two Factions under one Allegiance.')]
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

        $membership->update(['faction_id' => $request->validated('faction_id')]);

        $attendee = $membership->attendee()->firstOrFail();

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
