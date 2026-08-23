<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
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
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee', 'integer', 'The id of the attendee.', example: 1)]
    #[UrlParam('member', 'integer', 'The id of the Player to remove.', example: 1)]
    #[Response(description: 'The member was removed from the Attendee.')]
    public function __invoke(Request $request, Event $event, EventAttendee $attendee, User $member): JsonResponse
    {
        Gate::authorize('changeMembers', $attendee);

        $attendee->members()->detach($member->getKey());

        return response()->json(status: 200);
    }
}
