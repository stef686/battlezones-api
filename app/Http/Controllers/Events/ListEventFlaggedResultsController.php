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
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ListEventFlaggedResultsController extends Controller
{
    #[Endpoint('List Flagged Results', 'Organisers only. The open flags on this Event, oldest first, with the Game and its current scores.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(['data' => [[
        'id' => 3,
        'game_id' => 18,
        'reason' => 'We agreed 85-70 but it went in the other way round.',
        'flagged_at' => '2026-09-12T14:20:00+00:00',
        'flagged_by' => ['id' => 12, 'name' => 'Ada Lovelace'],
        'game' => [
            'id' => 18,
            'table_number' => 5,
            'is_bye' => false,
            'round' => ['id' => 4, 'number' => 2, 'name' => 'Round 2'],
            'attendees' => [['id' => 9, 'name' => 'Ada and Grace', 'scores' => ['match-points' => 3, 'victory-points' => 85]]],
        ],
        'resolved_at' => null,
        'resolved_by' => null,
    ]]])]
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
