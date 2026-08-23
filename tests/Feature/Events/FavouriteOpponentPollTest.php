<?php

use App\Actions\Events\StoreGameScores;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventPoll;
use App\Models\EventVote;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use App\Notifications\Events\VotingOpenNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Score a Game between two Attendees, as a Player submission would.
 */
function scoreGame(Round $round, EventAttendee $home, EventAttendee $away, int $homeScore = 85, int $awayScore = 70): Game
{
    $scoreType = $round->event->scoreTypes()->where('slug', 'victory-points')->sole();

    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$home->id, $away->id]);

    app(StoreGameScores::class)->execute($game, [
        $home->id => [$scoreType->id => $homeScore],
        $away->id => [$scoreType->id => $awayScore],
    ]);

    return $game;
}

/**
 * A two-round Event with four Attendees, both Rounds played out.
 *
 * @return array{0: Event, 1: EventPoll, 2: User, 3: EventAttendee, 4: EventAttendee, 5: EventAttendee, 6: EventAttendee}
 */
function playedOutEvent(int $roundCount = 2): array
{
    $event = pairableEvent(['round_count' => $roundCount]);
    $poll = EventPoll::factory()->for($event)->favouriteOpponent()->create(['votes_per_player' => 1]);

    $voter = User::factory()->create();
    $own = EventAttendee::factory()->for($event)->withMember($voter)->create();
    [$first, $second, $third] = EventAttendee::factory()->count(3)->for($event)->withMember()->create()->all();

    $roundOne = Round::factory()->for($event)->live()->create(['number' => 1]);
    scoreGame($roundOne, $own, $first);
    scoreGame($roundOne, $second, $third);

    $roundTwo = Round::factory()->for($event)->live()->create(['number' => 2]);
    scoreGame($roundTwo, $own, $second);
    scoreGame($roundTwo, $first, $third);

    return [$event, $poll, $voter, $own, $first, $second, $third];
}

test('voting opens for an attendee that has a result in every round', function () {
    [$event, $poll, $voter, $own, $first, $second, $third] = playedOutEvent();

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$first->id],
        ])
        ->assertSuccessful();

    expect(EventVote::query()->where('event_poll_id', $poll->id)->count())->toBe(1);
});

test('an attendee that finished round three with round four ungenerated cannot vote', function () {
    [$event, $poll, $voter, $own, $first] = playedOutEvent(roundCount: 3);

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$first->id],
        ])
        ->assertStatus(422);

    expect(EventVote::query()->count())->toBe(0);
});

test('an attendee with a round still unscored cannot vote', function () {
    [$event, $poll, $voter, $own, $first, $second, $third] = playedOutEvent();

    $roundThree = Round::factory()->for($event)->live()->create(['number' => 3]);
    $event->update(['settings' => $event->settings->with(['round_count' => 3])]);

    $game = Game::factory()->for($roundThree)->create();
    $game->attendees()->attach([$own->id, $third->id]);

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$first->id],
        ])
        ->assertStatus(422);
});

test('candidates are limited to the opponents actually played', function () {
    [$event, $poll, $voter, $own, $first, $second, $third] = playedOutEvent();

    $response = $this->actingAs($voter)
        ->getJson(route('events.polls.candidates', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    expect(collect($response->json('data'))->pluck('id')->sort()->values()->all())
        ->toEqual(collect([$first->id, $second->id])->sort()->values()->all());

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$third->id],
        ])
        ->assertJsonValidationErrors('attendee_ids.0');
});

test('a bye reduces the candidate list rather than appearing in it', function () {
    $event = pairableEvent(['round_count' => 2]);
    $poll = EventPoll::factory()->for($event)->favouriteOpponent()->create();

    $voter = User::factory()->create();
    $own = EventAttendee::factory()->for($event)->withMember($voter)->create();
    [$first, $second] = EventAttendee::factory()->count(2)->for($event)->withMember()->create()->all();

    $roundOne = Round::factory()->for($event)->live()->create(['number' => 1]);
    scoreGame($roundOne, $own, $first);
    $bye = Game::factory()->for($roundOne)->bye()->create();
    $bye->attendees()->attach($second->id);
    app(StoreGameScores::class)->awardByeWin($bye);

    $roundTwo = Round::factory()->for($event)->live()->create(['number' => 2]);
    scoreGame($roundTwo, $first, $second);
    $ownBye = Game::factory()->for($roundTwo)->bye()->create();
    $ownBye->attendees()->attach($own->id);
    app(StoreGameScores::class)->awardByeWin($ownBye);

    $response = $this->actingAs($voter)
        ->getJson(route('events.polls.candidates', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    expect(collect($response->json('data'))->pluck('id')->all())->toEqual([$first->id]);
});

test('one force-close closes voting for every attendee', function () {
    [$event, $poll, $voter, $own, $first] = playedOutEvent();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->postJson(route('events.polls.close', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$first->id],
        ])
        ->assertStatus(422);
});

test('tallies stay organiser-only', function () {
    [$event, $poll, $voter] = playedOutEvent();

    $this->actingAs($voter)
        ->getJson(route('events.polls.results', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertForbidden();

    $this->actingAs(organiserOf($event))
        ->getJson(route('events.polls.results', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();
});

test('a team is told once when its voting opens', function () {
    Notification::fake();

    $event = pairableEvent(['round_count' => 1]);
    $poll = EventPoll::factory()->for($event)->favouriteOpponent()->create();

    $player = User::factory()->create();
    $own = EventAttendee::factory()->for($event)->withMember($player)->create();
    $opponent = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create(['number' => 1]);
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$own->id, $opponent->id]);

    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->sole();

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $own->id => ['victory-points' => 85],
                $opponent->id => ['victory-points' => 70],
            ],
        ])
        ->assertSuccessful();

    expect($victoryPoints->slug)->toBe('victory-points');

    Notification::assertSentTo($player, VotingOpenNotification::class, fn (VotingOpenNotification $notification): bool => $notification->poll->is($poll));
    Notification::assertSentToTimes($player, VotingOpenNotification::class, 1);
});
