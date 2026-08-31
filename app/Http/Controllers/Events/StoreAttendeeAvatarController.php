<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreAttendeeAvatarRequest;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Services\AttendeeAvatarService;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreAttendeeAvatarController extends Controller
{
    public function __construct(private AttendeeAvatarService $avatars) {}

    #[Endpoint(
        'Upload a Team Avatar',
        'The team and its Organisers. A multipart route of its own rather than a field on the Attendee PATCH, because PHP does not populate uploaded files for PATCH bodies. The upload is cropped to a 256x256 WebP square and the original is discarded.'
    )]
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
    public function __invoke(
        StoreAttendeeAvatarRequest $request,
        Event $event,
        EventAttendee $attendee,
    ): EventAttendeeDetailResource {
        $this->avatars->replace($attendee, $request->file('avatar'));

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
