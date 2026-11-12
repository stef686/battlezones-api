<?php

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;
use App\Models\User;

/**
 * A team already sitting in a Live Round's Game, with an opponent.
 *
 * @return array{0: Event, 1: EventAttendee, 2: Game}
 */
function pairedTeam(): array
{
    $event = Event::factory()->active()->create(['attendee_size' => 2]);
    $attendee = EventAttendee::factory()->for($event)->withMember(User::factory())->create(['name' => 'Ada and Grace']);
    $opponent = EventAttendee::factory()->for($event)->withMember(User::factory())->create();

    $game = Game::factory()->for(Round::factory()->for($event)->live()->create(['number' => 1]))->create();
    $game->attendees()->attach([$attendee->id, $opponent->id]);

    return [$event, $attendee, $game];
}

function dropAttendeeRoute(Event $event, EventAttendee $attendee): string
{
    return route('events.attendees.drop', ['event' => $event->slug, 'attendee' => $attendee->id]);
}

test('an organiser drops a team that has been paired, and the drop is on the team', function () {
    [$event, $attendee] = pairedTeam();

    $droppedAt = $this->actingAs(coOrganiserOf($event))
        ->postJson(dropAttendeeRoute($event, $attendee))
        ->assertSuccessful()
        ->json('data.dropped_at');

    expect($droppedAt)->toBeString()
        ->and($attendee->refresh()->isDropped())->toBeTrue();
});

test('a platform admin drops a team too, so the panel keeps working', function () {
    [$event, $attendee] = pairedTeam();

    $this->actingAs(User::factory()->admin()->create())
        ->postJson(dropAttendeeRoute($event, $attendee))
        ->assertSuccessful();

    expect($attendee->refresh()->isDropped())->toBeTrue();
});

test('players cannot drop a team, their own included', function (Closure $reader) {
    [$event, $attendee] = pairedTeam();

    $this->actingAs($reader($event, $attendee))
        ->postJson(dropAttendeeRoute($event, $attendee))
        ->assertForbidden();

    expect($attendee->refresh()->isDropped())->toBeFalse();
})->with([
    'its own player' => [fn (Event $event, EventAttendee $attendee): User => $attendee->members()->firstOrFail()],
    'another player' => [fn (Event $event): User => playerOf($event)],
]);

test('an event only its organisers can see answers 404 to anyone else', function (EventStatus $status) {
    [$event, $attendee] = pairedTeam();
    $event->update(['status' => $status]);

    $this->actingAs(playerOf($event))
        ->postJson(dropAttendeeRoute($event, $attendee))
        ->assertNotFound();
})->with('hidden event statuses');

test('a team that has never been paired is refused, pointing at delete', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember()->create();

    $this->actingAs(coOrganiserOf($event))
        ->postJson(dropAttendeeRoute($event, $attendee))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['attendee' => 'Delete it instead']);

    expect($attendee->refresh()->isDropped())->toBeFalse();
});

test('a team already dropped is refused', function () {
    [$event, $attendee] = pairedTeam();
    $attendee->forceFill(['dropped_at' => now()->subHour()])->save();
    $droppedAt = $attendee->dropped_at?->toIso8601String();

    $this->actingAs(coOrganiserOf($event))
        ->postJson(dropAttendeeRoute($event, $attendee))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['attendee' => 'already been dropped']);

    expect($attendee->refresh()->dropped_at?->toIso8601String())->toBe($droppedAt);
});

test('a drop alters no game, score or round', function () {
    [$event, $attendee, $game] = pairedTeam();
    GameScore::factory()->for($game)->create([
        'event_attendee_id' => $attendee->id,
        'event_score_type_id' => EventScoreType::factory()->for($event),
        'value' => 15,
    ]);

    $before = [
        $game->round->refresh()->toArray(),
        $game->refresh()->toArray(),
        $game->scores()->get()->toArray(),
        $game->attendees()->pluck('event_attendees.id')->all(),
    ];

    $this->actingAs(coOrganiserOf($event))
        ->postJson(dropAttendeeRoute($event, $attendee))
        ->assertSuccessful();

    expect([
        $game->round->refresh()->toArray(),
        $game->refresh()->toArray(),
        $game->scores()->get()->toArray(),
        $game->attendees()->pluck('event_attendees.id')->all(),
    ])->toEqual($before);
});
