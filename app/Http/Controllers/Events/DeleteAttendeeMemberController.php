<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class DeleteAttendeeMemberController extends Controller
{
    #[Endpoint('Remove a Player from a Team', 'Closed to members once registration closes; Organisers are never blocked.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[UrlParam('membership_id', 'integer', 'The id of the seat to empty.', example: 4)]
    #[Response(description: 'The member was removed from the Attendee.')]
    public function __invoke(
        Request $request,
        Event $event,
        EventAttendee $attendee,
        EventAttendeeMembership $membership,
    ): JsonResponse {
        Gate::authorize('changeMembers', $attendee);

        // The seat rather than the Player, because the Player most likely to
        // be dropped is one who never answered their invitation, and an
        // unclaimed account is deliberately unresolvable by route.
        $attendee->members()->detach($membership->user_id);

        return response()->json(status: 200);
    }
}
