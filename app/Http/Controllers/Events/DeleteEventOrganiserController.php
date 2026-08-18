<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class DeleteEventOrganiserController extends Controller
{
    #[Endpoint('Remove an Organiser', 'Lead organisers only.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('user', 'integer', 'The id of the organiser to remove.', example: 1)]
    public function __invoke(Request $request, Event $event, User $user): JsonResponse
    {
        Gate::authorize('manageOrganisers', $event);

        abort_unless($event->isOrganisedBy($user), 404);

        $event->organisers()->detach($user->getKey());

        return response()->json(status: 200);
    }
}
