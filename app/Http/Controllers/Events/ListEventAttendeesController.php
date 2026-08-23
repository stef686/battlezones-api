<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ListEventAttendeesRequest;
use App\Http\Resources\Events\EventAttendeeResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ListEventAttendeesController extends Controller
{
    #[Endpoint('List Event Attendees', 'Paginated list of attendees for a publicly visible event.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(EventAttendeeResource::class, model: EventAttendee::class, paginate: 15)]
    public function __invoke(ListEventAttendeesRequest $request, Event $event): AnonymousResourceCollection
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $attendees = $event->attendees()
            ->with(['memberships.user.clubs', 'memberships.faction'])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('search').'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('event_attendees.name', 'like', $term)
                        ->orWhereHas('memberships.user', fn (Builder $q) => $q->where('users.name', 'like', $term))
                        ->orWhereHas('memberships.user.clubs', fn (Builder $q) => $q->where('clubs.name', 'like', $term))
                        ->orWhereHas('memberships.faction', fn (Builder $q) => $q->where('factions.name', 'like', $term));
                });
            })
            ->orderByRaw('COALESCE(event_attendees.name, (
                SELECT MIN(users.name)
                FROM event_attendee_user
                JOIN users ON users.id = event_attendee_user.user_id
                WHERE event_attendee_user.event_attendee_id = event_attendees.id
            ))')
            ->paginate();

        return EventAttendeeResource::collection($attendees);
    }
}
