<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateEventAttendeeRequest;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateEventAttendeeController extends Controller
{
    #[Endpoint('Amend a Team', 'Members and Organisers may change the party name; allegiance freezes once a Round is Live.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[Response(status: 200, content: ['data' => [
        'id' => 9,
        'name' => 'Ada and Grace',
        'allegiance' => 'loyalist',
        'members' => [[
            'id' => 12,
            'name' => 'Ada Lovelace',
            'faction' => ['id' => 3, 'name' => 'Sons of Horus'],
            'army_list_locked' => true,
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
    public function __invoke(
        UpdateEventAttendeeRequest $request,
        Event $event,
        EventAttendee $attendee,
    ): EventAttendeeDetailResource {
        Gate::authorize('update', $attendee);

        $attendee->fill($request->validated())->save();

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
