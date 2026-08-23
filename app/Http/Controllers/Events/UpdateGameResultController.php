<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\StoreGameScores;
use App\Events\ResultEdited;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateGameResultRequest;
use App\Http\Resources\Events\GameDetailResource;
use App\Models\Event;
use App\Models\Game;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateGameResultController extends Controller
{
    public function __construct(private StoreGameScores $storeGameScores) {}

    #[Endpoint('Correct a Game Result', 'Organisers only, at any point and in any Round. Correcting is separate from resolving a flag: an Organiser who corrects a result still has to close the flag, and one who finds the result was right can close it without touching the scores.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('game', 'integer', 'The id of the game.', example: 1)]
    #[Response(['data' => [
        'id' => 18,
        'table_number' => 5,
        'is_bye' => false,
        'round' => ['id' => 4, 'number' => 2, 'name' => 'Round 2'],
        'result' => [
            'submitted_at' => '2026-09-12T14:05:00+00:00',
            'submitted_by' => ['id' => 12, 'name' => 'Ada Lovelace'],
            'edited_at' => null,
            'edited_by' => null,
            'is_flagged' => false,
        ],
        'attendees' => [[
            'id' => 9,
            'name' => 'Ada and Grace',
            'members' => [['id' => 12, 'name' => 'Ada Lovelace', 'faction' => ['id' => 3, 'name' => 'Sons of Horus'], 'army_list' => 'Legion Tactical Squad, 10 models...']],
            'scores' => ['match-points' => 3, 'victory-points' => 85],
        ]],
    ]])]
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

        ResultEdited::dispatch($game->refresh(), $request->user());

        $game->load(['round', 'attendees.memberships.user', 'attendees.memberships.faction', 'scores.scoreType', 'submittedBy', 'editedBy', 'openResultFlag']);

        return GameDetailResource::make($game);
    }
}
