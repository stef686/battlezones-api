<?php

use App\Events\PollOpened;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventPoll;
use App\Models\EventVote;
use App\Models\User;
use App\Notifications\Events\VotingOpenNotification;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;

test('an organiser creates a painting poll, opens it, and closes it again', function () {
    $event = Event::factory()->active()->create();
    $organiser = organiserOf($event);

    $poll = $this->actingAs($organiser)
        ->postJson(route('events.polls.store', ['event' => $event->slug]), [
            'name' => 'Best Painted Army',
            'type' => 'painting',
            'votes_per_player' => 3,
        ])
        ->assertSuccessful()
        ->json('data');

    expect($poll['is_open'])->toBeFalse()
        ->and($poll['votes_per_player'])->toBe(3);

    $this->actingAs($organiser)
        ->postJson(route('events.polls.open', ['event' => $event->slug, 'poll' => $poll['id']]))
        ->assertSuccessful()
        ->assertJsonPath('data.is_open', true);

    $this->actingAs($organiser)
        ->postJson(route('events.polls.close', ['event' => $event->slug, 'poll' => $poll['id']]))
        ->assertSuccessful()
        ->assertJsonPath('data.is_open', false);

    expect(EventPoll::query()->find($poll['id'])->closes_at)->not->toBeNull();
});

test('players cannot create or open a poll', function () {
    $event = Event::factory()->active()->create();
    $player = User::factory()->create();
    $poll = EventPoll::factory()->for($event)->create();

    $this->actingAs($player)
        ->postJson(route('events.polls.store', ['event' => $event->slug]), [
            'name' => 'Rigged Vote',
            'type' => 'painting',
        ])
        ->assertForbidden();

    $this->actingAs($player)
        ->postJson(route('events.polls.open', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertForbidden();
});

test('an organiser marks entry and display number independently', function () {
    $event = Event::factory()->active()->create();
    $organiser = organiserOf($event);
    $attendee = EventAttendee::factory()->for($event)->withMember()->create();

    $this->actingAs($organiser)
        ->patchJson(route('events.attendees.painting.update', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'painting_entered' => true,
        ])
        ->assertSuccessful();

    expect($attendee->refresh()->painting_entered)->toBeTrue()
        ->and($attendee->display_number)->toBeNull();

    $this->actingAs($organiser)
        ->patchJson(route('events.attendees.painting.update', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'display_number' => 14,
        ])
        ->assertSuccessful();

    expect($attendee->refresh()->display_number)->toBe(14)
        ->and($attendee->painting_entered)->toBeTrue();
});

/**
 * An open painting Poll with three entered Attendees and a voter.
 *
 * @return array{0: Event, 1: EventPoll, 2: User, 3: EventAttendee, 4: EventAttendee, 5: EventAttendee}
 */
function paintingPoll(int $votesPerPlayer = 2): array
{
    $event = Event::factory()->active()->create();

    $poll = EventPoll::factory()->for($event)->open()->create(['votes_per_player' => $votesPerPlayer]);

    $voter = User::factory()->create();
    $own = EventAttendee::factory()->for($event)->withMember($voter)->create(['painting_entered' => true]);
    $first = EventAttendee::factory()->for($event)->withMember()->create(['painting_entered' => true]);
    $second = EventAttendee::factory()->for($event)->withMember()->create(['painting_entered' => true]);

    return [$event, $poll, $voter, $own, $first, $second];
}

test('a player replaces their whole ballot in one call', function () {
    [$event, $poll, $voter, $own, $first, $second] = paintingPoll();

    $url = route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]);

    $this->actingAs($voter)
        ->putJson($url, ['attendee_ids' => [$first->id, $second->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.attendee_ids', [$first->id, $second->id]);

    $this->actingAs($voter)
        ->putJson($url, ['attendee_ids' => [$second->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.attendee_ids', [$second->id]);

    expect(EventVote::query()->where('event_poll_id', $poll->id)->where('voter_user_id', $voter->id)->pluck('subject_event_attendee_id')->all())
        ->toEqual([$second->id]);
});

test('a ballot longer than the limit is rejected', function () {
    [$event, $poll, $voter, $own, $first, $second] = paintingPoll(votesPerPlayer: 1);

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$first->id, $second->id],
        ])
        ->assertJsonValidationErrors('attendee_ids');

    expect(EventVote::query()->count())->toBe(0);
});

test('a player cannot pick their own attendee or one that is not entered', function () {
    [$event, $poll, $voter, $own, $first] = paintingPoll();

    $notEntered = EventAttendee::factory()->for($event)->withMember()->create(['painting_entered' => false]);

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$own->id],
        ])
        ->assertJsonValidationErrors('attendee_ids.0');

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$notEntered->id],
        ])
        ->assertJsonValidationErrors('attendee_ids.0');

    expect(EventVote::query()->count())->toBe(0);
});

test('the same attendee cannot be picked twice on one ballot', function () {
    [$event, $poll, $voter, $own, $first] = paintingPoll();

    $this->actingAs($voter)
        ->putJson(route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]), [
            'attendee_ids' => [$first->id, $first->id],
        ])
        ->assertJsonValidationErrors('attendee_ids.0');
});

test('voting is rejected before the poll opens and after it closes', function () {
    [$event, $poll, $voter, $own, $first] = paintingPoll();

    $poll->forceFill(['opens_at' => null, 'closes_at' => null])->save();

    $url = route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]);

    $this->actingAs($voter)
        ->putJson($url, ['attendee_ids' => [$first->id]])
        ->assertStatus(422);

    $poll->forceFill(['opens_at' => now()->subHours(2), 'closes_at' => now()->subHour()])->save();

    $this->actingAs($voter)
        ->putJson($url, ['attendee_ids' => [$first->id]])
        ->assertStatus(422);

    expect(EventVote::query()->count())->toBe(0);
});

test('both players of a doubles team vote independently', function () {
    [$event, $poll, $voter, $own, $first, $second] = paintingPoll();

    $partner = User::factory()->create();
    $own->memberships()->create(['user_id' => $partner->id, 'event_id' => $event->id]);

    $url = route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]);

    $this->actingAs($voter)->putJson($url, ['attendee_ids' => [$first->id]])->assertSuccessful();
    $this->actingAs($partner)->putJson($url, ['attendee_ids' => [$second->id]])->assertSuccessful();

    expect(EventVote::query()->where('event_poll_id', $poll->id)->count())->toBe(2);
});

test('organisers read tallies and ties come back unresolved', function () {
    [$event, $poll, $voter, $own, $first, $second] = paintingPoll(votesPerPlayer: 1);
    $organiser = organiserOf($event);

    $otherVoter = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($otherVoter)->create(['painting_entered' => true]);

    $url = route('events.polls.ballot.update', ['event' => $event->slug, 'poll' => $poll->id]);

    $this->actingAs($voter)->putJson($url, ['attendee_ids' => [$first->id]])->assertSuccessful();
    $this->actingAs($otherVoter)->putJson($url, ['attendee_ids' => [$second->id]])->assertSuccessful();

    $response = $this->actingAs($organiser)
        ->getJson(route('events.polls.results', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    $tallies = collect($response->json('data.tallies'));

    expect($tallies->firstWhere('attendee.id', $first->id)['votes'])->toBe(1)
        ->and($tallies->firstWhere('attendee.id', $second->id)['votes'])->toBe(1)
        ->and($response->json('data'))->not->toHaveKey('winner')
        ->and($tallies->first())->not->toHaveKey('position');
});

test('players never read tallies, open or closed', function () {
    [$event, $poll, $voter, $own, $first] = paintingPoll();

    $this->actingAs($voter)
        ->getJson(route('events.polls.results', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertForbidden();

    $poll->forceFill(['closes_at' => now()->subMinute()])->save();

    $this->actingAs($voter)
        ->getJson(route('events.polls.results', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertForbidden();
});

test('opening a poll fires a poll opened event and tells the players voting is open', function () {
    Notification::fake();

    $event = Event::factory()->active()->create();
    $organiser = organiserOf($event);
    $poll = EventPoll::factory()->for($event)->create();

    $player = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($player)->create();

    EventFacade::fake([PollOpened::class]);

    $this->actingAs($organiser)
        ->postJson(route('events.polls.open', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    EventFacade::assertDispatched(PollOpened::class, fn (PollOpened $dispatched): bool => $dispatched->poll->is($poll));
});

test('the voting-open notification reaches players in app', function () {
    Notification::fake();

    $event = Event::factory()->active()->create();
    $organiser = organiserOf($event);
    $poll = EventPoll::factory()->for($event)->create();

    $player = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($player)->create();

    $this->actingAs($organiser)
        ->postJson(route('events.polls.open', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    Notification::assertSentTo($player, VotingOpenNotification::class, function (VotingOpenNotification $notification) use ($player, $poll): bool {
        $payload = $notification->toArray($player);

        return $payload['poll_id'] === $poll->id
            && in_array('database', $notification->via($player), true);
    });
});

test('players list the polls of an event without seeing any tallies', function () {
    [$event, $poll, $voter] = paintingPoll();

    EventPoll::factory()->for($event)->favouriteOpponent()->create();

    $response = $this->actingAs($voter)
        ->getJson(route('events.polls.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    expect(collect($response->json('data'))->firstWhere('id', $poll->id)['is_open'])->toBeTrue()
        ->and($response->json('data.0'))->not->toHaveKey('tallies');
});
