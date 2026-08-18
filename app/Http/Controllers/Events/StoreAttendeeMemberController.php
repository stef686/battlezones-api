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
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreAttendeeMemberController extends Controller
{
    #[Endpoint('Add a Player to a Team', 'Invites the Player if they have no account yet. Closed to members once registration closes; Organisers are never blocked.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee', 'integer', 'The id of the attendee.', example: 1)]
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
