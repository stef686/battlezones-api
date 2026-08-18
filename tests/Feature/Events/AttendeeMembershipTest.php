<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Faction;
use App\Models\User;
use App\Notifications\Events\EventInviteNotification;
use Illuminate\Support\Facades\Notification;

/**
 * @return array{0: Event, 1: EventAttendee, 2: User, 3: Faction}
 */
function teamOfOneSoFar(array $eventAttributes = []): array
{
    $event = Event::factory()->published()->create([
        'attendee_size' => 2,
        ...$eventAttributes,
    ]);
    $captain = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($captain)->create(['name' => 'Sons of Terra']);

    return [$event, $attendee, $captain, Faction::factory()->create(['game_system_id' => $event->game_system_id])];
}

test('a captain adds their partner, who is invited', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'name' => 'Tarik Torgaddon',
            'email' => 'tarik@example.com',
            'faction_id' => $faction->id,
        ])
        ->assertCreated();

    $partner = User::where('email', 'tarik@example.com')->firstOrFail();

    expect($attendee->fresh()->members)->toHaveCount(2)
        ->and($partner->isClaimed())->toBeFalse();

    Notification::assertSentTo($partner, EventInviteNotification::class);
});

test('a party cannot grow past the size the event competes in', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar();
    $attendee->members()->attach(User::factory()->create(), ['event_id' => $event->id]);

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'email' => 'third@example.com',
            'faction_id' => $faction->id,
        ])
        ->assertUnprocessable();

    expect($attendee->fresh()->members)->toHaveCount(2);
});

test('membership stops changing when registration closes', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar(['registration_closes_at' => now()->subHour()]);

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'email' => 'tarik@example.com',
            'faction_id' => $faction->id,
        ])
        ->assertForbidden();

    expect($attendee->fresh()->members)->toHaveCount(1);
});

test('an organiser amends membership after the deadline', function () {
    Notification::fake();

    [$event, $attendee, , $faction] = teamOfOneSoFar(['registration_closes_at' => now()->subHour()]);
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'name' => 'Late Replacement',
            'email' => 'late@example.com',
            'faction_id' => $faction->id,
        ])
        ->assertCreated();

    expect($attendee->fresh()->members)->toHaveCount(2);
});

test('a captain drops a partner before the deadline', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    $partner = User::factory()->create();
    $attendee->members()->attach($partner, ['event_id' => $event->id]);

    $this->actingAs($captain)
        ->deleteJson(route('events.attendees.members.destroy', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'member' => $partner->id,
        ]))
        ->assertSuccessful();

    expect($attendee->fresh()->members)->toHaveCount(1);
});
