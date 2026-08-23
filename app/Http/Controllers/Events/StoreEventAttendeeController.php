<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\RegisterAttendee;
use App\Enums\Allegiance;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventAttendeeRequest;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreEventAttendeeController extends Controller
{
    #[Endpoint('Register a Team', 'Enters a party for the Event, inviting any Player who has no account yet.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(
        StoreEventAttendeeRequest $request,
        Event $event,
        RegisterAttendee $registerAttendee,
    ): JsonResponse {
        Gate::authorize('register', $event);

        /** @var list<array{name?: string|null, email: string, faction_id?: int|null, army_list?: string|null}> $players */
        $players = $request->validated('players');

        $attendee = $registerAttendee->handle(
            event: $event,
            players: $players,
            registeredBy: $request->user(),
            name: $request->validated('name'),
            allegiance: Allegiance::tryFrom((string) $request->validated('allegiance')),
        );

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee)
            ->response()
            ->setStatusCode(201);
    }
}
