<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\GameResultFlagResource;
use App\Models\Event;
use App\Models\GameResultFlag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ListEventFlaggedResultsController extends Controller
{
    #[Endpoint('List Flagged Results', 'Organisers only. The open flags on this Event, oldest first, with the Game and its current scores.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(Event $event): AnonymousResourceCollection
    {
        Gate::authorize('organise', $event);

        $flags = GameResultFlag::query()
            ->unresolved()
            ->whereHas('game.round', fn (Builder $query) => $query->where('event_id', $event->getKey()))
            ->with(['flaggedBy', 'game.round', 'game.attendees.memberships.user', 'game.scores.scoreType'])
            ->oldest()
            ->paginate();

        return GameResultFlagResource::collection($flags);
    }
}
