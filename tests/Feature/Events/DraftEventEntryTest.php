<?php

use App\Enums\EventStatus;
use App\Enums\RegistrationMode;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use App\Models\EventInvite;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

/**
 * An open singles Event, entry closing in a week.
 */
function draftEntryEvent(EventStatus $status): Event
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
function draftEntrySingles(string $email): array
{
    return ['players' => [['email' => $email]]];
}

/**
 * A team of three in a Draft, as one is once its Event goes back to Draft:
 * a claimed Captain and a partner still waiting on their invitation.
 *
 * @return array{event: Event, attendee: EventAttendee, captain: User, waiting: EventAttendeeMembership}
 */
function draftEntryTeam(): array
{
    $event = draftEntryEvent(EventStatus::Draft);
    $event->forceFill(['attendee_size' => 3])->save();

    $captain = User::factory()->create();
    $partner = User::factory()->unclaimed()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($captain)->create();
    $attendee->members()->attach($partner, ['event_id' => $event->id]);

    return [
        'event' => $event,
        'attendee' => $attendee,
        'captain' => $captain,
        'waiting' => $attendee->memberships()->where('user_id', $partner->id)->sole(),
    ];
}

test('an organiser cannot enter anybody into a draft, and is told to publish first', function () {
    $event = draftEntryEvent(EventStatus::Draft);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.attendees.store', $event), draftEntrySingles('straggler@example.com'))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Publish the event first.');

    expect($event->attendees()->exists())->toBeFalse()
        ->and(User::query()->where('email', 'straggler@example.com')->exists())->toBeFalse();
});

test('an organiser cannot invite a captain into a draft, and nobody is emailed', function () {
    Notification::fake();

    $event = draftEntryEvent(EventStatus::Draft);

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

    ['event' => $event, 'attendee' => $attendee] = draftEntryTeam();

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'email' => 'partner@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Publish the event first.');

    expect($attendee->members()->count())->toBe(2);

    Notification::assertNothingSent();
});

test('an organiser cannot send a waiting partner their invitation again while the event is a draft', function () {
    Notification::fake();

    ['event' => $event, 'attendee' => $attendee, 'waiting' => $waiting] = draftEntryTeam();

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.attendees.members.invite', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $waiting->id,
        ]))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Publish the event first.');

    Notification::assertNothingSent();
});

test('a team member of a draft cannot find it to add a player to, rather than being told to publish it', function () {
    Notification::fake();

    ['event' => $event, 'attendee' => $attendee, 'captain' => $captain] = draftEntryTeam();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'email' => 'partner@example.com',
        ])
        ->assertNotFound();

    Notification::assertNothingSent();
});

test('a team member of a draft cannot find it to chase a partner, rather than being told to publish it', function () {
    Notification::fake();

    ['event' => $event, 'attendee' => $attendee, 'captain' => $captain, 'waiting' => $waiting] = draftEntryTeam();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.invite', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $waiting->id,
        ]))
        ->assertNotFound();

    Notification::assertNothingSent();
});

test('a player cannot find a draft to enter, rather than learning it exists', function () {
    $event = draftEntryEvent(EventStatus::Draft);
    $player = User::factory()->create();

    $this->actingAs($player)
        ->postJson(route('events.attendees.store', $event), draftEntrySingles($player->email))
        ->assertNotFound();

    expect($event->attendees()->exists())->toBeFalse();
});

test('a stranger cannot find a draft to invite into', function () {
    $event = draftEntryEvent(EventStatus::Draft);

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.invites.store', $event), ['email' => 'captain@example.com'])
        ->assertNotFound();
});

test('entering an event that is under way works as it always has', function (EventStatus $status) {
    Notification::fake();

    $event = draftEntryEvent($status);
    $player = User::factory()->create();

    $this->actingAs($player)
        ->postJson(route('events.attendees.store', $event), draftEntrySingles($player->email))
        ->assertCreated();
})->with([EventStatus::Published, EventStatus::Active, EventStatus::Completed]);

test('inviting into an event that is under way works as it always has', function (EventStatus $status) {
    Notification::fake();

    $event = draftEntryEvent($status);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.invites.store', $event), ['email' => 'captain@example.com'])
        ->assertCreated();
})->with([EventStatus::Published, EventStatus::Active, EventStatus::Completed]);
