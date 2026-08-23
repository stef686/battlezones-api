<?php

use App\Actions\Events\GenerateRoundPairings;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Score a Game through the Player submission endpoint and hand back the pieces.
 *
 * @return array{0: Event, 1: Game, 2: EventAttendee, 3: EventAttendee, 4: User}
 */
function submittedGame(?User $player = null): array
{
    $event = Event::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 1]);

    $player ??= User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    test()->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertSuccessful();

    return [$event, $game->refresh(), $mine, $theirs, $player];
}

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
