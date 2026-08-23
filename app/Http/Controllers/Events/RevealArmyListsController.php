<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class RevealArmyListsController extends Controller
{
    #[Endpoint('Reveal a Team\'s Army Lists', 'Organisers only. Frees a team held hostage by a Player who never submitted.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee', 'integer', 'The id of the attendee.', example: 1)]
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
        'custom_field_responses' => [['id' => 1, 'name' => 'Dietary requirements', 'type' => 'text', 'value' => 'None']],
        'games' => [[
            'id' => 18,
            'round_number' => 2,
            'table_number' => 5,
            'is_bye' => false,
            'scores' => ['match-points' => 3, 'victory-points' => 85],
            'opponents' => [['id' => 11, 'name' => 'Grace and Alan']],
        ]],
    ]])]
    public function __invoke(Request $request, Event $event, EventAttendee $attendee): EventAttendeeDetailResource
    {
        Gate::authorize('organise', $event);

        $attendee->forceFill(['army_lists_revealed_at' => now()])->save();

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
