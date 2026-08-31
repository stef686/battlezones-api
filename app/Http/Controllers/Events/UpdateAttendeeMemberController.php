<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\AmendAttendeeMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateAttendeeMemberRequest;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateAttendeeMemberController extends Controller
{
    #[Endpoint(
        'Amend an Invited Player',
        'The name, address and Faction of a team mate who has not claimed their account. Refused once they have: their details are then theirs alone. Addressed by membership because an unclaimed account is not addressable by route.'
    )]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[UrlParam('membership_id', 'integer', 'The id of the membership being amended.', example: 4)]
    #[Response(status: 200, content: ['data' => [
        'id' => 9,
        'name' => 'Ada and Grace',
        'allegiance' => 'loyalist',
        'members' => [[
            'id' => 12,
            'name' => 'Ada Lovelace',
            'faction' => ['id' => 3, 'name' => 'Sons of Horus'],
            'army_list_locked' => false,
            'membership_id' => 4,
            'invite_outstanding' => true,
            'clubs' => [],
        ]],
        'checked_in_at' => null,
        'custom_field_responses' => [],
        'games' => [],
    ]])]
    #[Response(['message' => 'That player has claimed their account, so only they can change their details.'], 403, 'The Player has an account of their own.')]
    public function __invoke(
        UpdateAttendeeMemberRequest $request,
        Event $event,
        EventAttendee $attendee,
        EventAttendeeMembership $membership,
        AmendAttendeeMember $amendAttendeeMember,
    ): EventAttendeeDetailResource {
        Gate::authorize('changeMembers', $attendee);

        abort_if(
            $membership->user->isClaimed(),
            403,
            'That player has claimed their account, so only they can change their details.',
        );

        /** @var array{name?: string|null, email?: string, faction_id?: int|null} $changes */
        $changes = $request->validated();

        $amendAttendeeMember->handle($membership, $changes, $request->user());

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
