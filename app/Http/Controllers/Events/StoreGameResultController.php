<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\StoreGameScores;
use App\Events\ResultSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\SubmitGameResultRequest;
use App\Http\Resources\Events\GameDetailResource;
use App\Models\Event;
use App\Models\Game;
use App\Models\User;
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
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('game', 'integer', 'The id of the game.', example: 1)]
    #[Response(['message' => 'A result has already been submitted for this game. Flag it if it needs correcting.'], 409, 'A result already exists.')]
    public function __invoke(SubmitGameResultRequest $request, Event $event, Game $game): GameDetailResource
    {
        $scoresByAttendee = $request->scoresByAttendeeId();

        /** @var User $submitter */
        $submitter = $request->user();

        DB::transaction(function () use ($game, $submitter, $scoresByAttendee): void {
            $this->claimSubmission($game, $submitter);

            $this->storeGameScores->execute($game, $scoresByAttendee);
        });

        ResultSubmitted::dispatch($game->refresh(), $submitter);

        $game->load(['round', 'attendees.memberships.user', 'attendees.memberships.faction', 'scores.scoreType', 'submittedBy', 'editedBy', 'openResultFlag']);

        return GameDetailResource::make($game);
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

        abort_if(
            $claimed === 0,
            409,
            'A result has already been submitted for this game. Flag it if it needs correcting.',
        );
    }
}
