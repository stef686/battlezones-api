<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\RoundDetailResource;
use App\Models\Event;
use App\Models\Round;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ShowEventRoundController extends Controller
{
    #[Endpoint('Show Event Round', 'Round detail with games for an event.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('round', 'integer', 'The id of the round.', example: 1)]
    public function __invoke(Event $event, Round $round): RoundDetailResource
    {
        abort_unless($event->status->hasRoundsVisible(), 404);

        $round->load(['games' => fn ($q) => $q->orderBy('table_number'), 'games.attendees.user', 'games.attendees.faction']);

        return RoundDetailResource::make($round);
    }
}
