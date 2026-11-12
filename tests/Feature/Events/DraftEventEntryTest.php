<?php

use App\Enums\EventStatus;
use App\Enums\RegistrationMode;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

/**
 * An open singles Event, entry closing in a week.
 */
function eventIn(EventStatus $status): Event
{
    return Event::factory()->create([
        'status' => $status,
        'attendee_size' => 1,
        'registration_mode' => RegistrationMode::Open,
        'registration_closes_at' => now()->addWeek(),
    ]);
}

/**
 * @return array{players: list<array{email: string}>}
 */
function singlesEntry(string $email): array
{
    return ['players' => [['email' => $email]]];
}

test('an organiser cannot enter anybody into a draft, and is told to publish first', function () {
    $event = eventIn(EventStatus::Draft);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.attendees.store', $event), singlesEntry('straggler@example.com'))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Publish the event first.');

    expect($event->attendees()->exists())->toBeFalse()
        ->and(User::query()->where('email', 'straggler@example.com')->exists())->toBeFalse();
});

test('an organiser cannot invite a captain into a draft, and nobody is emailed', function () {
    Notification::fake();

    $event = eventIn(EventStatus::Draft);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.invites.store', $event), ['email' => 'captain@example.com'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Publish the event first.');

    expect(EventInvite::query()->exists())->toBeFalse()
        ->and(User::query()->where('email', 'captain@example.com')->exists())->toBeFalse();

    Notification::assertNothingSent();
});

test('nobody is added to a team in a draft, which a team can be in once an event goes back to draft', function () {
    Notification::fake();

    $event = eventIn(EventStatus::Draft);
    $event->forceFill(['attendee_size' => 2])->save();
    $attendee = EventAttendee::factory()->for($event)->withMember(User::factory()->create())->create();

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'email' => 'partner@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Publish the event first.');

    expect($attendee->members()->count())->toBe(1);

    Notification::assertNothingSent();
});

test('a player cannot find a draft to enter, rather than learning it exists', function () {
    $event = eventIn(EventStatus::Draft);
    $player = User::factory()->create();

    $this->actingAs($player)
        ->postJson(route('events.attendees.store', $event), singlesEntry($player->email))
        ->assertNotFound();

    expect($event->attendees()->exists())->toBeFalse();
});

test('a stranger cannot find a draft to invite into', function () {
    $event = eventIn(EventStatus::Draft);

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.invites.store', $event), ['email' => 'captain@example.com'])
        ->assertNotFound();
});

test('entering an event that is under way works as it always has', function (EventStatus $status) {
    Notification::fake();

    $event = eventIn($status);
    $player = User::factory()->create();

    $this->actingAs($player)
        ->postJson(route('events.attendees.store', $event), singlesEntry($player->email))
        ->assertCreated();
})->with([EventStatus::Published, EventStatus::Active, EventStatus::Completed]);

test('inviting into an event that is under way works as it always has', function (EventStatus $status) {
    Notification::fake();

    $event = eventIn($status);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.invites.store', $event), ['email' => 'captain@example.com'])
        ->assertCreated();
})->with([EventStatus::Published, EventStatus::Active, EventStatus::Completed]);
