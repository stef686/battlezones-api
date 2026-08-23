<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\ArmyListResource;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UnlockArmyListController extends Controller
{
    #[Endpoint('Unlock an Army List', 'Organisers only. Locking has no other escape, and a wrong list matters for every opponent who prepares against it.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee', 'integer', 'The id of the attendee.', example: 1)]
    #[UrlParam('member', 'integer', 'The id of the Player whose list to reopen.', example: 1)]
    #[Response(['data' => [
        'army_list' => 'Legion Tactical Squad, 10 models...',
        'submitted_at' => '2026-09-10T18:30:00+00:00',
        'is_locked' => false,
    ]])]
    public function __invoke(Request $request, Event $event, EventAttendee $attendee, User $member): ArmyListResource
    {
        Gate::authorize('organise', $event);

        $membership = $attendee->memberships()->where('user_id', $member->getKey())->firstOrFail();

        $membership->forceFill(['army_list_submitted_at' => null])->save();

        return ArmyListResource::make($membership);
    }
}
