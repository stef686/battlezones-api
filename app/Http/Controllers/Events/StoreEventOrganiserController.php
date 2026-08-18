<?php

namespace App\Http\Controllers\Events;

use App\Enums\EventOrganiserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventOrganiserRequest;
use App\Http\Resources\Events\EventOrganiserResource;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreEventOrganiserController extends Controller
{
    #[Endpoint('Appoint an Organiser', 'Lead organisers only. The account must already be claimed.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(StoreEventOrganiserRequest $request, Event $event): EventOrganiserResource
    {
        Gate::authorize('manageOrganisers', $event);

        $user = $request->organiser();

        $event->organisers()->syncWithoutDetaching([
            $user->getKey() => ['role' => EventOrganiserRole::Organiser->value],
        ]);

        return EventOrganiserResource::make($event->organisers()->whereKey($user->getKey())->firstOrFail());
    }
}
