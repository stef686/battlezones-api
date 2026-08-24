<?php

namespace App\Http\Controllers\Events;

use App\Enums\RoundStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPoll;
use App\Models\GameScore;
use App\Models\Round;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * What has moved, so the client knows what to refetch.
 *
 * The SPA stays current during a live Event by polling rather than by
 * WebSockets, and polling the fat resources — Standings for 32 Attendees,
 * the Rounds, my Game — every twenty seconds means a full recompute per
 * Player per poll. This answers with change stamps only, and the client
 * refetches the expensive resource when the stamp it cares about moves.
 *
 * There is no stamp for My Game: it changes when its Round is published or
 * when its result lands, which are the rounds and standings stamps. A stamp
 * of its own would have to be computed per viewer, which is the cost this
 * endpoint exists to avoid.
 */
#[Group('Events', 'APIs for Events')]
class ShowEventPulseController extends Controller
{
    #[Endpoint('Event Pulse', 'Change stamps for the live-critical resources of an Event. Cheap enough to poll: four aggregates, no recomputation, and a fixed number of queries however large the Event.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(['data' => [
        'current_round' => ['id' => 4, 'number' => 2],
        'rounds' => '2026-09-12T13:30:00Z',
        'standings' => '2026-09-12T14:05:12Z',
        'polls' => '2026-09-12T16:00:00Z',
    ]])]
    #[ResponseField('data.current_round', 'object', 'The highest-numbered Live Round, or null before any Round is published.', nullable: true)]
    #[ResponseField('data.rounds', 'string', 'When a Round was last created, published or unpublished. Null when the Event has no Rounds.', nullable: true)]
    #[ResponseField('data.standings', 'string', 'When a score last changed. Null before any result is in.', nullable: true)]
    #[ResponseField('data.polls', 'string', 'When a Poll was last opened, closed or changed. Null when the Event has no Polls.', nullable: true)]
    public function __invoke(Event $event): JsonResponse
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $currentRound = Round::query()
            ->where('event_id', $event->getKey())
            ->where('status', RoundStatus::Live)
            ->orderByDesc('number')
            ->first(['id', 'number']);

        return response()->json([
            'data' => [
                'current_round' => $currentRound === null ? null : [
                    'id' => $currentRound->id,
                    'number' => $currentRound->number,
                ],
                'rounds' => $this->stamp(
                    Round::query()->where('event_id', $event->getKey())->max('updated_at'),
                ),
                // Standings are aggregated from the scores, so the scores are
                // what the stamp watches: a correction that leaves the Game row
                // alone still moves them.
                'standings' => $this->stamp(
                    GameScore::query()
                        ->join('games', 'games.id', '=', 'game_scores.game_id')
                        ->join('rounds', 'rounds.id', '=', 'games.round_id')
                        ->where('rounds.event_id', $event->getKey())
                        ->max('game_scores.updated_at'),
                ),
                'polls' => $this->stamp(
                    EventPoll::query()->where('event_id', $event->getKey())->max('updated_at'),
                ),
            ],
        ]);
    }

    /**
     * A stamp the client can compare as a string, or null when there is
     * nothing of that kind yet.
     */
    private function stamp(mixed $value): ?string
    {
        return $value === null ? null : Carbon::parse((string) $value)->toIso8601ZuluString();
    }
}
