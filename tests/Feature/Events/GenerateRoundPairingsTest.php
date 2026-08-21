<?php

use App\Actions\Events\GenerateRoundPairings;
use App\Actions\Events\StoreGameScores;
use App\Enums\Allegiance;
use App\Enums\EventOrganiserRole;
use App\Enums\RoundStatus;
use App\Exceptions\CannotGeneratePairings;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

function generatePairings(Event $event): Round
{
    return app(GenerateRoundPairings::class)->execute($event);
}

/**
 * An Event that ranks on Match Points, with Victory Points as the tiebreaker.
 *
 * @param  array<string, mixed>  $settings
 */
function pairableEvent(array $settings = []): Event
{
    $event = Event::factory()->active()->settings($settings)->create();

    EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create(['display_order' => 0]);
    EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 1]);

    return $event;
}

/**
 * Play a Round to a fixed set of results, deriving Match Points as a real
 * submission would.
 *
 * @param  array<int, array{EventAttendee, int, EventAttendee, int}>  $results
 * @param  array<int, EventAttendee>  $byes
 */
function playRound(Event $event, int $number, array $results, array $byes = []): Round
{
    $round = Round::factory()->for($event)->live()->create(['number' => $number]);
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->sole();

    foreach ($results as $index => [$home, $homeScore, $away, $awayScore]) {
        $game = $round->games()->create(['table_number' => $index + 1, 'is_bye' => false]);
        $game->attendees()->attach([$home->id, $away->id]);

        app(StoreGameScores::class)->execute($game, [
            $home->id => [$victoryPoints->id => $homeScore],
            $away->id => [$victoryPoints->id => $awayScore],
        ]);
    }

    foreach ($byes as $attendee) {
        $round->games()->create(['table_number' => null, 'is_bye' => true])
            ->attendees()->attach($attendee->id);
    }

    return $round;
}

/**
 * @return Collection<int, EventAttendee>
 */
function attendeesFor(Event $event, int $count, ?Allegiance $allegiance = null): Collection
{
    return EventAttendee::factory()
        ->count($count)
        ->for($event)
        ->withMember()
        ->create(['allegiance' => $allegiance]);
}

/**
 * @return array<int, array<int, int>> the attendee id pairs of each Game, each pair sorted
 */
function pairingsOf(Round $round): array
{
    return $round->games()->with('attendees')->get()
        ->map(fn (Game $game): array => $game->attendees->pluck('id')->sort()->values()->all())
        ->sortBy(fn (array $pair): int => $pair[0])
        ->values()
        ->all();
}

test('pairings are generated into a new draft round', function () {
    $event = Event::factory()->active()->create();
    EventAttendee::factory()->count(4)->for($event)->withMember()->create();

    $round = generatePairings($event);

    expect($round->number)->toBe(1)
        ->and($round->status)->toBe(RoundStatus::Draft)
        ->and($round->games()->count())->toBe(2);
});

test('every game opposes allegiances where the event requires it', function () {
    $event = Event::factory()->active()->settings(['requires_opposed_allegiance' => true])->create();
    attendeesFor($event, 3, Allegiance::Loyalist);
    attendeesFor($event, 3, Allegiance::Traitor);

    $round = generatePairings($event);

    expect($round->games()->count())->toBe(3);

    $round->load('games.attendees');

    foreach ($round->games as $game) {
        expect($game->attendees->pluck('allegiance')->unique())->toHaveCount(2);
    }
});

test('attendees on equal scores are paired together', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    [$l1, $l2, $l3, $l4] = attendeesFor($event, 4, Allegiance::Loyalist)->all();
    [$t1, $t2, $t3, $t4] = attendeesFor($event, 4, Allegiance::Traitor)->all();

    playRound($event, 1, [
        [$l1, 10, $t1, 20],
        [$l2, 20, $t2, 10],
        [$l3, 20, $t3, 10],
        [$l4, 10, $t4, 20],
    ]);

    $round = generatePairings($event);

    expect($round->number)->toBe(2);

    $winners = [$l2->id, $l3->id, $t1->id, $t4->id];

    foreach ($round->games()->with('attendees')->get() as $game) {
        $wonLastRound = $game->attendees->map(fn (EventAttendee $a): bool => in_array($a->id, $winners, true));

        expect($wonLastRound->unique())->toHaveCount(1);
    }
});

test('rematches are avoided where any alternative exists', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    [$l1, $l2, $l3, $l4] = attendeesFor($event, 4, Allegiance::Loyalist)->all();
    [$t1, $t2, $t3, $t4] = attendeesFor($event, 4, Allegiance::Traitor)->all();

    // Every Game is drawn, so the field stays in one score group and the only
    // thing left for the matcher to weigh is who has already met whom.
    playRound($event, 1, [[$l1, 15, $t1, 15], [$l2, 15, $t2, 15], [$l3, 15, $t3, 15], [$l4, 15, $t4, 15]]);
    playRound($event, 2, [[$l1, 15, $t4, 15], [$l2, 15, $t1, 15], [$l3, 15, $t2, 15], [$l4, 15, $t3, 15]]);
    playRound($event, 3, [[$l1, 15, $t3, 15], [$l2, 15, $t4, 15], [$l3, 15, $t1, 15], [$l4, 15, $t2, 15]]);

    // After three rounds each Loyalist has one Traitor left, so exactly one
    // rematch-free round exists and the matcher has to find it.
    expect(pairingsOf(generatePairings($event)))->toBe([
        [$l1->id, $t2->id],
        [$l2->id, $t3->id],
        [$l3->id, $t4->id],
        [$l4->id, $t1->id],
    ]);
});

test('a rematch is permitted once the field has no alternative', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    [$l1, $l2] = attendeesFor($event, 2, Allegiance::Loyalist)->all();
    [$t1, $t2] = attendeesFor($event, 2, Allegiance::Traitor)->all();

    playRound($event, 1, [[$l1, 15, $t1, 15], [$l2, 15, $t2, 15]]);
    playRound($event, 2, [[$l1, 15, $t2, 15], [$l2, 15, $t1, 15]]);

    // Both Loyalists have now met both Traitors, so a rematch is the only
    // round available. The penalty is finite so that a pairing still comes back.
    $round = generatePairings($event);

    expect($round->number)->toBe(3)
        ->and(pairingsOf($round))->toBe([
            [$l1->id, $t1->id],
            [$l2->id, $t2->id],
        ]);
});

test('the bye goes to the lowest-ranked attendee of the majority allegiance', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    [, , $l3] = attendeesFor($event, 3, Allegiance::Loyalist)->all();
    attendeesFor($event, 2, Allegiance::Traitor);

    $round = generatePairings($event);
    $bye = $round->games()->where('is_bye', true)->with('attendees')->sole();

    expect($round->games()->count())->toBe(3)
        ->and($bye->attendees->pluck('id')->all())->toBe([$l3->id])
        ->and($bye->table_number)->toBeNull();
});

test('the bye skips an attendee who has already had one', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    [$l1, $l2, $l3] = attendeesFor($event, 3, Allegiance::Loyalist)->all();
    [$t1, $t2] = attendeesFor($event, 2, Allegiance::Traitor)->all();

    // l3 sits out the first round, and l2 finishes it below l1 on Match Points.
    playRound($event, 1, [[$l1, 20, $t1, 10], [$l2, 10, $t2, 20]], [$l3]);

    $round = generatePairings($event);
    $bye = $round->games()->where('is_bye', true)->with('attendees')->sole();

    // l3 is still the lowest-ranked Loyalist, so the bye passes up to l2.
    expect($bye->attendees->pluck('id')->all())->toBe([$l2->id]);
});

test('generation is blocked while a result is missing from the current round', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    [$l1, $l2] = attendeesFor($event, 2, Allegiance::Loyalist)->all();
    [$t1, $t2] = attendeesFor($event, 2, Allegiance::Traitor)->all();

    $round = playRound($event, 1, [[$l1, 20, $t1, 10]]);
    $round->games()->create(['table_number' => 2, 'is_bye' => false])
        ->attendees()->attach([$l2->id, $t2->id]);

    expect(fn () => generatePairings($event))
        ->toThrow(CannotGeneratePairings::class, 'Round 1 still has results outstanding');

    expect($event->rounds()->count())->toBe(1);
});

test('generation is blocked while the current round is still in draft', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    attendeesFor($event, 2, Allegiance::Loyalist);
    attendeesFor($event, 2, Allegiance::Traitor);

    generatePairings($event);

    expect(fn () => generatePairings($event))
        ->toThrow(CannotGeneratePairings::class, 'Round 1 has not been published yet');

    expect($event->rounds()->count())->toBe(1);
});

test('generation refuses to exceed the round count', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true, 'round_count' => 2]);
    [$l1] = attendeesFor($event, 1, Allegiance::Loyalist)->all();
    [$t1] = attendeesFor($event, 1, Allegiance::Traitor)->all();

    playRound($event, 1, [[$l1, 20, $t1, 10]]);
    playRound($event, 2, [[$l1, 10, $t1, 20]]);

    expect(fn () => generatePairings($event))
        ->toThrow(CannotGeneratePairings::class, 'scheduled for 2 rounds');

    expect($event->rounds()->count())->toBe(2);
});

/**
 * Twenty Loyalists against twenty Traitors, with thirteen Loyalists winning the
 * first Round — the split the Horus Heresy doubles field actually produces.
 *
 * @return array{Event, Collection<int, EventAttendee>, Collection<int, EventAttendee>}
 */
function lopsidedField(): array
{
    $event = pairableEvent(['requires_opposed_allegiance' => true]);

    $loyalists = EventAttendee::factory()->count(20)->for($event)->create(['allegiance' => Allegiance::Loyalist]);
    $traitors = EventAttendee::factory()->count(20)->for($event)->create(['allegiance' => Allegiance::Traitor]);

    $results = [];

    foreach ($loyalists as $index => $loyalist) {
        $loyalistWins = $index < 13;

        $results[] = [$loyalist, $loyalistWins ? 20 : 10, $traitors[$index], $loyalistWins ? 10 : 20];
    }

    playRound($event, 1, $results);

    return [$event, $loyalists, $traitors];
}

test('a lopsided allegiance split pairs down as little as the field allows', function () {
    [$event, $loyalists, $traitors] = lopsidedField();

    $round = generatePairings($event);

    $winners = $loyalists->take(13)->pluck('id')
        ->concat($traitors->skip(13)->pluck('id'))
        ->all();

    $games = $round->games()->with('attendees')->get();

    expect($games)->toHaveCount(20);

    $pairDowns = 0;

    foreach ($games as $game) {
        $allegiances = $game->attendees->pluck('allegiance')->unique();
        $records = $game->attendees->map(fn (EventAttendee $a): bool => in_array($a->id, $winners, true))->unique();

        expect($allegiances)->toHaveCount(2);

        if ($records->count() === 2) {
            $pairDowns++;
        }
    }

    // Thirteen Loyalist winners chase only seven Traitor winners, so six Games
    // have to cross the score groups. Six is the floor, not an accident.
    expect($pairDowns)->toBe(6);
});

test('the same field always pairs the same way', function () {
    // Compared by position in the field rather than by id, so that two runs of
    // the same fixture are comparable despite holding different Attendees.
    $pairAndTable = function (): array {
        [$event, $loyalists, $traitors] = lopsidedField();

        $position = $loyalists->concat($traitors)->pluck('id')->flip();

        return generatePairings($event)->games()->with('attendees')->get()
            ->map(fn (Game $game): array => [
                $game->table_number,
                ...$game->attendees->map(fn (EventAttendee $a): int => $position[$a->id])->sort()->values()->all(),
            ])
            ->sortBy(fn (array $game): int => $game[0])
            ->values()
            ->all();
    };

    expect($pairAndTable())->toBe($pairAndTable());
});

test('an attendee with no allegiance blocks an opposed event', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    attendeesFor($event, 2, Allegiance::Loyalist);
    attendeesFor($event, 1, Allegiance::Traitor);
    attendeesFor($event, 1);

    expect(fn () => generatePairings($event))
        ->toThrow(CannotGeneratePairings::class, 'no Allegiance');

    expect($event->rounds()->count())->toBe(0);
});

test('a one-sided field cannot be paired where allegiances must oppose', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    attendeesFor($event, 4, Allegiance::Loyalist);

    expect(fn () => generatePairings($event))
        ->toThrow(CannotGeneratePairings::class, 'shares one Allegiance');

    expect($event->rounds()->count())->toBe(0);
});

test('a field of one cannot be paired', function () {
    $event = pairableEvent();
    attendeesFor($event, 1);

    expect(fn () => generatePairings($event))
        ->toThrow(CannotGeneratePairings::class, 'too few Attendees');

    expect($event->rounds()->count())->toBe(0);
});

function pairingOrganiserOf(Event $event): User
{
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    return $organiser;
}

test('an organiser generates the next round', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    attendeesFor($event, 2, Allegiance::Loyalist);
    attendeesFor($event, 2, Allegiance::Traitor);

    $this->actingAs(pairingOrganiserOf($event))
        ->postJson(route('events.rounds.generate', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.number', 1)
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonCount(2, 'data.games');

    expect($event->rounds()->count())->toBe(1);
});

test('a player may not generate a round', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    attendeesFor($event, 2, Allegiance::Loyalist);
    attendeesFor($event, 2, Allegiance::Traitor);

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.rounds.generate', ['event' => $event->slug]))
        ->assertForbidden();

    expect($event->rounds()->count())->toBe(0);
});

test('a field that cannot be paired answers with the reason', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    attendeesFor($event, 4, Allegiance::Loyalist);

    $this->actingAs(pairingOrganiserOf($event))
        ->postJson(route('events.rounds.generate', ['event' => $event->slug]))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Every Attendee shares one Allegiance, so no Game can be opposed.');
});
