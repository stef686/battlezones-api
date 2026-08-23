<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\SubmitArmyListRequest;
use App\Http\Resources\Events\ArmyListResource;
use App\Models\Event;
use App\Models\EventAttendeeMembership;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateArmyListController extends Controller
{
    #[Endpoint('Submit Your Army List', 'Submitting locks the list. Only an Organiser can reopen it.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(['data' => [
        'army_list' => 'Legion Tactical Squad, 10 models...',
        'submitted_at' => '2026-09-10T18:30:00+00:00',
        'is_locked' => false,
    ]])]
    public function __invoke(SubmitArmyListRequest $request, Event $event): ArmyListResource
    {
        $membership = EventAttendeeMembership::query()
            ->where('event_id', $event->getKey())
            ->where('user_id', $request->user()->getKey())
            ->first();

        abort_if($membership === null, 404);

        abort_if(
            $membership->isArmyListLocked(),
            403,
            'Your list is locked. Ask an organiser to reopen it if it needs correcting.',
        );

        $membership->forceFill([
            'army_list' => $request->string('army_list')->toString(),
            'army_list_submitted_at' => now(),
        ])->save();

        return ArmyListResource::make($membership);
    }
}
