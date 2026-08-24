<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\EnrolPlayer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreAttendeeMemberRequest;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreAttendeeMemberController extends Controller
{
    #[Endpoint('Add a Player to a Team', 'Invites the Player if they have no account yet. Closed to members once registration closes; Organisers are never blocked.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[Response(status: 201, content: ['data' => [
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
        StoreAttendeeMemberRequest $request,
        Event $event,
        EventAttendee $attendee,
        EnrolPlayer $enrolPlayer,
    ): JsonResponse {
        Gate::authorize('changeMembers', $attendee);

        /** @var array{name?: string|null, email: string, faction_id?: int|null, army_list?: string|null} $player */
        $player = $request->validated();

        $enrolPlayer->handle($attendee, $player, $request->user());

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee)
            ->response()
            ->setStatusCode(201);
    }
}
