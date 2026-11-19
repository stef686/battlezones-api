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
        'Amend a team mate',
        'The name, address and Faction of a team mate. Once they have claimed their account, their name and address are theirs alone, and only an Organiser can still correct their Faction. The Faction also locks with the Player\'s army list: once it is submitted, only an Organiser can change or clear it. Addressed by membership because the membership is the seat being amended.'
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
    #[Response(['message' => 'That player has claimed their account, so only they can change their details.'], 403, 'The Player has an account of their own, and the caller is not an Organiser.')]
    #[Response(['message' => 'That player has claimed their account, so only they can change their name and email.'], 403, 'An Organiser sent a name or email for a Player with an account of their own.')]
    #[Response(['message' => 'Their list is locked. Ask an organiser to reopen it if their Faction needs changing.'], 403, 'A team mate sent a Faction for a Player whose army list is locked.')]
    public function __invoke(
        UpdateAttendeeMemberRequest $request,
        Event $event,
        EventAttendee $attendee,
        EventAttendeeMembership $membership,
        AmendAttendeeMember $amendAttendeeMember,
    ): EventAttendeeDetailResource {
        Gate::authorize('changeMembers', $attendee);

        $mayCorrectFaction = Gate::allows('correctMemberFaction', $attendee);

        if ($membership->user->isClaimed()) {
            $this->ensureClaimedPlayerMayBeAmended($request, $mayCorrectFaction);
        }

        /** @var array{name?: string|null, email?: string, faction_id?: int|null} $changes */
        $changes = $request->validated();

        $amendAttendeeMember->handle(
            $membership,
            $changes,
            $request->user(),
            mayOverrideFactionLock: $mayCorrectFaction,
        );

        $attendee->loadForDetail();

        return EventAttendeeDetailResource::make($attendee);
    }

    /**
     * A claimed Player's name and address are theirs alone, but their Faction
     * belongs to their seat in the Event, so an Organiser can still correct it
     * on the day. A team mate cannot touch any of it.
     *
     * Name and email are refused on presence rather than change, so a caller
     * resending the value already held is told plainly rather than quietly
     * ignored.
     */
    private function ensureClaimedPlayerMayBeAmended(UpdateAttendeeMemberRequest $request, bool $mayCorrectFaction): void
    {
        abort_unless(
            $mayCorrectFaction,
            403,
            'That player has claimed their account, so only they can change their details.',
        );

        abort_if(
            $request->hasAny(['name', 'email']),
            403,
            'That player has claimed their account, so only they can change their name and email.',
        );
    }
}
