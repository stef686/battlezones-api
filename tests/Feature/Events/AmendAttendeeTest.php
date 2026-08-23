<?php

use App\Enums\Allegiance;
use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Round;
use App\Models\User;

test('a captain changes their allegiance while every round is still draft', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $captain = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($captain)
        ->create(['allegiance' => Allegiance::Loyalist]);

    Round::factory()->for($event)->create(['number' => 1]);

    $this->actingAs($captain)
        ->patchJson(route('events.attendees.update', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'allegiance' => 'traitor',
        ])
        ->assertSuccessful();

    expect($attendee->fresh()->allegiance)->toBe(Allegiance::Traitor);
});

test('allegiance freezes once a round goes live', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $captain = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($captain)
        ->create(['allegiance' => Allegiance::Loyalist]);

    Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->actingAs($captain)
        ->patchJson(route('events.attendees.update', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'allegiance' => 'traitor',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('allegiance');

    expect($attendee->fresh()->allegiance)->toBe(Allegiance::Loyalist);
});

test('an organiser cannot change allegiance once a round is live either', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);
    $attendee = EventAttendee::factory()->for($event)->withMember()
        ->create(['allegiance' => Allegiance::Loyalist]);

    Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->actingAs($organiser)
        ->patchJson(route('events.attendees.update', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'allegiance' => 'traitor',
        ])
        ->assertUnprocessable();

    expect($attendee->fresh()->allegiance)->toBe(Allegiance::Loyalist);
});

test('the team name still changes once a round is live', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $captain = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($captain)
        ->create(['name' => 'Sons of Terra', 'allegiance' => Allegiance::Loyalist]);

    Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->actingAs($captain)
        ->patchJson(route('events.attendees.update', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'name' => 'Sons of Horus',
        ])
        ->assertSuccessful();

    expect($attendee->fresh()->name)->toBe('Sons of Horus');
});

test('a stranger cannot amend someone else\'s team', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $attendee = EventAttendee::factory()->for($event)->withMember()->create(['name' => 'Sons of Terra']);

    $this->actingAs(User::factory()->create())
        ->patchJson(route('events.attendees.update', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'name' => 'Hijacked',
        ])
        ->assertForbidden();

    expect($attendee->fresh()->name)->toBe('Sons of Terra');
});

test('allegiance is published with the team', function () {
    $event = Event::factory()->active()->create(['attendee_size' => 2]);
    $attendee = EventAttendee::factory()->for($event)->withMember()
        ->create(['name' => 'Sons of Terra', 'allegiance' => Allegiance::Traitor]);

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.allegiance', 'traitor');

    $this->getJson(route('events.attendees.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.allegiance', 'traitor');
});
