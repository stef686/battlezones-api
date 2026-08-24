<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\FactionResource;
use App\Models\Event;
use App\Models\Faction;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ListEventFactionsController extends Controller
{
    #[Endpoint('List the Factions on offer', "Every Faction in this Event's game system, for the picker a Player records theirs with.")]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(status: 200, content: ['data' => [
        ['id' => 3, 'name' => 'Sons of Horus', 'slug' => 'sons-of-horus'],
        ['id' => 4, 'name' => 'Imperial Fists', 'slug' => 'imperial-fists'],
    ]])]
    public function __invoke(Event $event): AnonymousResourceCollection
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        // Read from the Event rather than the game system directly: a Player
        // choosing a Faction is choosing within the Event they are entering,
        // and the SPA has the Event slug and nothing else.
        $factions = Faction::query()
            ->where('game_system_id', $event->game_system_id)
            ->orderBy('name')
            ->get();

        return FactionResource::collection($factions);
    }
}
