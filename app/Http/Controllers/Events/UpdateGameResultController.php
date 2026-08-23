<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\StoreGameScores;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateGameResultRequest;
use App\Http\Resources\Events\GameDetailResource;
use App\Models\Event;
use App\Models\Game;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateGameResultController extends Controller
{
    public function __construct(private StoreGameScores $storeGameScores) {}

    #[Endpoint('Correct a Game Result', 'Organisers only, at any point and in any Round. Correcting is separate from resolving a flag: an Organiser who corrects a result still has to close the flag, and one who finds the result was right can close it without touching the scores.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('game', 'integer', 'The id of the game.', example: 1)]
    public function __invoke(UpdateGameResultRequest $request, Event $event, Game $game): GameDetailResource
    {
        $scoresByAttendee = $request->scoresByAttendeeId();

        DB::transaction(function () use ($game, $request, $scoresByAttendee): void {
            $this->storeGameScores->execute($game, $scoresByAttendee);

            $game->forceFill([
                'edited_by_user_id' => $request->user()->getKey(),
                'edited_at' => now(),
            ])->save();
        });

        $game->load(['round', 'attendees.memberships.user', 'attendees.memberships.faction', 'scores.scoreType', 'submittedBy', 'editedBy', 'openResultFlag']);

        return GameDetailResource::make($game);
    }
}
