<?php

use App\Enums\CustomFieldType;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventCustomField;
use App\Models\EventCustomFieldResponse;
use App\Models\Faction;
use App\Models\User;

test('it returns attendee detail with user, faction, clubs, army_list, checked_in_at and empty games', function () {
    $event = Event::factory()->published()->create();
    $faction = Faction::factory()->create(['name' => 'Space Marines']);
    $club = Club::factory()->create(['name' => 'London Warlords']);

    $user = User::factory()->create(['name' => 'Alice Example']);
    $user->clubs()->attach($club);

    $attendee = EventAttendee::factory()
        ->for($event)
        ->for($user)
        ->for($faction)
        ->create([
            'army_list' => '1500pts Ultramarines list...',
            'checked_in_at' => '2026-04-12 09:30:00',
        ]);

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $attendee->id)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.name', 'Alice Example')
        ->assertJsonPath('data.faction.name', 'Space Marines')
        ->assertJsonPath('data.clubs.0.id', $club->id)
        ->assertJsonPath('data.clubs.0.name', 'London Warlords')
        ->assertJsonPath('data.army_list', '1500pts Ultramarines list...')
        ->assertJsonPath('data.checked_in_at', '2026-04-12T09:30:00Z')
        ->assertJsonPath('data.games', []);
});

test('games is always an explicit empty array until games are implemented', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $response = $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['games']]);

    expect($response->json('data.games'))->toBe([]);
});

test('it returns 404 when the attendee belongs to a different event', function () {
    $event = Event::factory()->published()->create();
    $otherEvent = Event::factory()->published()->create();

    $attendee = EventAttendee::factory()->for($otherEvent)->create();

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertNotFound();
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('it returns 404 for a nonexistent attendee id', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => 999999]))
        ->assertNotFound();
});

test('it is a public endpoint requiring no auth', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful();
});

test('it returns custom field responses for a text field', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $field = EventCustomField::factory()->for($event)->create([
        'name' => 'Preferred Table',
        'type' => CustomFieldType::Text,
        'display_order' => 1,
    ]);

    EventCustomFieldResponse::factory()
        ->for($attendee, 'attendee')
        ->for($field, 'field')
        ->create(['value' => 'Near the window']);

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.custom_field_responses.0.id', $field->id)
        ->assertJsonPath('data.custom_field_responses.0.name', 'Preferred Table')
        ->assertJsonPath('data.custom_field_responses.0.type', 'text')
        ->assertJsonPath('data.custom_field_responses.0.value', 'Near the window');
});

test('custom field responses are ordered by display_order', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $second = EventCustomField::factory()->for($event)->create([
        'name' => 'Second',
        'display_order' => 2,
    ]);
    $first = EventCustomField::factory()->for($event)->create([
        'name' => 'First',
        'display_order' => 1,
    ]);

    EventCustomFieldResponse::factory()->for($attendee, 'attendee')->for($second, 'field')->create(['value' => 'b']);
    EventCustomFieldResponse::factory()->for($attendee, 'attendee')->for($first, 'field')->create(['value' => 'a']);

    $response = $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful();

    expect(collect($response->json('data.custom_field_responses'))->pluck('name')->all())
        ->toEqual(['First', 'Second']);
});

test('attendee with no custom field responses returns an empty array', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    EventCustomField::factory()->for($event)->create();

    $response = $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['custom_field_responses']]);

    expect($response->json('data.custom_field_responses'))->toBe([]);
});

test('checkbox custom field values are returned as booleans', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $yesField = EventCustomField::factory()->for($event)->create([
        'name' => 'Attending Dinner',
        'type' => CustomFieldType::Checkbox,
        'display_order' => 1,
    ]);
    $noField = EventCustomField::factory()->for($event)->create([
        'name' => 'Needs Parking',
        'type' => CustomFieldType::Checkbox,
        'display_order' => 2,
    ]);

    EventCustomFieldResponse::factory()->for($attendee, 'attendee')->for($yesField, 'field')->create(['value' => '1']);
    EventCustomFieldResponse::factory()->for($attendee, 'attendee')->for($noField, 'field')->create(['value' => '0']);

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.custom_field_responses.0.value', true)
        ->assertJsonPath('data.custom_field_responses.1.value', false);
});

test('number custom field values are returned as integers', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $field = EventCustomField::factory()->for($event)->create([
        'name' => 'Points Level',
        'type' => CustomFieldType::Number,
    ]);

    EventCustomFieldResponse::factory()->for($attendee, 'attendee')->for($field, 'field')->create(['value' => '2000']);

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.custom_field_responses.0.value', 2000);
});

test('it does not leak custom field responses from other attendees', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();
    $otherAttendee = EventAttendee::factory()->for($event)->create();

    $field = EventCustomField::factory()->for($event)->create(['name' => 'Army Points']);

    EventCustomFieldResponse::factory()
        ->for($otherAttendee, 'attendee')
        ->for($field, 'field')
        ->create(['value' => 'should not appear']);

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.custom_field_responses', []);
});
