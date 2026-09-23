<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\SendEventInvite;
use App\Enums\EventInviteRole;
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
class ResendAttendeeInviteController extends Controller
{
    #[Endpoint(
        'Send a Team Mate Their Invitation Again',
        'A fresh credential to the address already on file. Refused once the Player has claimed their account, which is when they no longer need one.'
    )]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[UrlParam('membership_id', 'integer', 'The id of the membership being chased.', example: 4)]
    #[Response(description: 'The invitation was sent again.')]
    #[Response(['message' => 'That player has claimed their account, so they no longer need an invitation.'], 403, 'The Player has an account of their own.')]
    public function __invoke(
        Request $request,
        Event $event,
        EventAttendee $attendee,
        EventAttendeeMembership $membership,
        SendEventInvite $sendEventInvite,
    ): JsonResponse {
        Gate::authorize('changeMembers', $attendee);

        abort_if(
            $membership->user->isClaimed(),
            403,
            'That player has claimed their account, so they no longer need an invitation.',
        );

        // The same write path as the first send, so the previous token stops
        // working: two live credentials for one seat is one more than anybody
        // needs, and the older one has been sitting in a mailbox for weeks.
        $sendEventInvite->handle(
            event: $event,
            email: $membership->user->email,
            role: EventInviteRole::Player,
            name: $membership->user->name,
            attendee: $attendee,
            invitedBy: $request->user(),
        );

        return response()->json(status: 200);
    }
}
