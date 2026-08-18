<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\SendEventInvite;
use App\Enums\EventInviteRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventInviteRequest;
use App\Http\Resources\Events\EventInviteResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreEventInviteController extends Controller
{
    #[Endpoint('Invite a Captain', 'Organisers only. Emails a time-limited credential for this Event.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(
        StoreEventInviteRequest $request,
        Event $event,
        SendEventInvite $sendEventInvite,
    ): JsonResponse {
        Gate::authorize('organise', $event);

        $invite = $sendEventInvite->handle(
            event: $event,
            email: $request->string('email')->toString(),
            role: EventInviteRole::Captain,
            name: $request->string('name')->toString() ?: null,
            invitedBy: $request->user(),
        );

        return EventInviteResource::make($invite->load('user'))
            ->response()
            ->setStatusCode(201);
    }
}
