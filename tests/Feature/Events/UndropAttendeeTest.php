<?php

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;

/**
 * A team that played Round 1 and was then dropped.
 *
 * @return array{0: Event, 1: EventAttendee}
 */
function droppedTeam(): array
{
    $event = pairableEvent();
    $event->update(['status' => EventStatus::Active]);
    $attendee = EventAttendee::factory()->for($event)->withMember(User::factory())->create(['dropped_at' => now()->subHour()]);
    $opponent = EventAttendee::factory()->for($event)->withMember(User::factory())->create();

    $game = Game::factory()->for(Round::factory()->for($event)->live()->create(['number' => 1]))->create();
    $game->attendees()->attach([$attendee->id, $opponent->id]);

    return [$event, $attendee];
}

function undropAttendeeRoute(Event $event, EventAttendee $attendee): string
{
    return route('events.attendees.undrop', ['event' => $event->slug, 'attendee' => $attendee->id]);
}

test('an organiser undrops a team, and the drop is cleared', function () {
    [$event, $attendee] = droppedTeam();

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(undropAttendeeRoute($event, $attendee))
        ->assertSuccessful()
        ->assertJsonPath('data.dropped_at', null);

    expect($attendee->refresh()->isDropped())->toBeFalse();
});

test('a platform admin undrops a team too, so the panel keeps working', function () {
    [$event, $attendee] = droppedTeam();

    $this->actingAs(User::factory()->admin()->create())
        ->deleteJson(undropAttendeeRoute($event, $attendee))
        ->assertSuccessful();

    expect($attendee->refresh()->isDropped())->toBeFalse();
});

test('players cannot undrop a team, their own included', function (Closure $reader) {
    [$event, $attendee] = droppedTeam();

    $this->actingAs($reader($event, $attendee))
        ->deleteJson(undropAttendeeRoute($event, $attendee))
        ->assertForbidden();

    expect($attendee->refresh()->isDropped())->toBeTrue();
})->with([
    'its own player' => [fn (Event $event, EventAttendee $attendee): User => $attendee->members()->firstOrFail()],
    'another player' => [fn (Event $event): User => playerOf($event)],
]);

test('an event only its organisers can see answers 404 to anyone else', function (EventStatus $status) {
    [$event, $attendee] = droppedTeam();
    $event->update(['status' => $status]);

    $this->actingAs(playerOf($event))
        ->deleteJson(undropAttendeeRoute($event, $attendee))
        ->assertNotFound();
})->with('hidden event statuses');

test('a team that is not dropped is refused', function () {
    [$event, $attendee] = droppedTeam();
    $attendee->forceFill(['dropped_at' => null])->save();

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(undropAttendeeRoute($event, $attendee))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['attendee' => 'has not been dropped']);
});

test('an undropped team is paired again in the next round generated', function () {
    $event = pairableEvent();
    $attendee = EventAttendee::factory()->for($event)->withMember()->create(['dropped_at' => now()->subHour()]);
    EventAttendee::factory()->for($event)->withMember()->create();

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(undropAttendeeRoute($event, $attendee))
        ->assertSuccessful();

    $paired = generatePairings($event)->games()->with('attendees')->get()->flatMap->attendees->pluck('id');

    expect($paired)->toContain($attendee->id);
});
