<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\StoreGameScores;
use App\Events\ResultSubmitted;
use App\Exceptions\ResultAlreadySubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\SubmitGameResultRequest;
use App\Http\Resources\Events\GameDetailResource;
use App\Models\Event;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreGameResultController extends Controller
{
    public function __construct(private StoreGameScores $storeGameScores) {}

    #[Endpoint('Submit a Game Result', 'Either Player in a Game submits scores for both Attendees. The first submission wins and locks the Game: a later one is rejected and the result has to be flagged for an Organiser instead. Derived Score Types such as Match Points are computed server-side.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('game_id', 'integer', 'The id of the game.', example: 1)]
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
    #[Response(content: [
        'message' => 'A result has already been submitted for this game. Flag it if it needs correcting.',
        'data' => [
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
        ],
    ], status: 409, description: 'A result already exists. The body carries the Game as it stands, so a client whose own submission lost its response can recognise it rather than reporting a dispute.')]
    public function __invoke(SubmitGameResultRequest $request, Event $event, Game $game): JsonResponse
    {
        $scoresByAttendee = $request->scoresByAttendeeId();

        /** @var User $submitter */
        $submitter = $request->user();

        try {
            DB::transaction(function () use ($game, $submitter, $scoresByAttendee): void {
                $this->claimSubmission($game, $submitter);

                $this->storeGameScores->execute($game, $scoresByAttendee);
            });
        } catch (ResultAlreadySubmitted $conflict) {
            // Answered with the Game as it stands, in the shape a successful
            // submission returns, so the client can tell its own lost-response
            // retry from a genuine disagreement without asking again.
            return $this->gameResponse($conflict->game, 409, $conflict->getMessage());
        }

        ResultSubmitted::dispatch($game->refresh(), $submitter);

        return $this->gameResponse($game, 200);
    }

    /**
     * The Game as the client should see it, success or conflict alike.
     */
    private function gameResponse(Game $game, int $status, ?string $message = null): JsonResponse
    {
        $game->load(['round', 'attendees.memberships.user', 'attendees.memberships.faction', 'scores.scoreType', 'submittedBy', 'editedBy', 'openResultFlag']);

        $resource = GameDetailResource::make($game);

        if ($message !== null) {
            $resource->additional(['message' => $message]);
        }

        return $resource->response()->setStatusCode($status);
    }

    /**
     * Claim the Game for this submission, or fail if another Player got there first.
     *
     * First submission wins: the conditional update is the whole race guard, so two
     * contradicting submissions cannot both land and silently overwrite one another.
     */
    private function claimSubmission(Game $game, User $submitter): void
    {
        $claimed = Game::query()
            ->whereKey($game->getKey())
            ->whereNull('submitted_at')
            ->update([
                'submitted_by_user_id' => $submitter->getKey(),
                'submitted_at' => now(),
            ]);

        if ($claimed === 0) {
            throw new ResultAlreadySubmitted($game->fresh());
        }
    }
}
