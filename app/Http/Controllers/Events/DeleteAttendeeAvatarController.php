<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Services\AttendeeAvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class DeleteAttendeeAvatarController extends Controller
{
    public function __construct(private AttendeeAvatarService $avatars) {}

    #[Endpoint('Remove a Team Avatar', 'The team and its Organisers. Deletes the stored square and returns the team to its placeholder.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[Response(status: 200, content: ['data' => [
        'id' => 9,
        'name' => 'Ada and Grace',
        'allegiance' => 'loyalist',
        'avatar' => 'https://uploads.example/avatars/9/8f1c….webp',
        'members' => [[
            'id' => 12,
            'name' => 'Ada Lovelace',
            'faction' => ['id' => 3, 'name' => 'Sons of Horus'],
            'army_list_locked' => false,
            'clubs' => [],
        ]],
        'checked_in_at' => null,
        'custom_field_responses' => [],
        'games' => [],
    ]])]
    public function __invoke(Request $request, Event $event, EventAttendee $attendee): EventAttendeeDetailResource
    {
        Gate::authorize('update', $attendee);

        $this->avatars->delete($attendee);

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
