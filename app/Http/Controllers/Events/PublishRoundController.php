<?php

namespace App\Http\Controllers\Events;

use App\Enums\RoundStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\RoundDetailResource;
use App\Models\Event;
use App\Models\Round;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class PublishRoundController extends Controller
{
    #[Endpoint('Publish a Round', 'Organisers only. Makes the Round\'s pairings and table numbers visible to Players. Earlier Rounds stay Live.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('round', 'integer', 'The id of the round.', example: 1)]
    public function __invoke(Event $event, Round $round): RoundDetailResource
    {
        Gate::authorize('organise', $event);

        $round->update(['status' => RoundStatus::Live]);

        $round->load(['games' => fn ($query) => $query->orderBy('table_number'), 'games.attendees.memberships.user', 'games.attendees.memberships.faction', 'games.scores.scoreType']);

        return RoundDetailResource::make($round);
    }
}
