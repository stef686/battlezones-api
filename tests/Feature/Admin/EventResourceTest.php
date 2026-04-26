<?php

use App\Enums\EventStatus;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\RelationManagers\AttendeesRelationManager;
use App\Filament\Resources\Events\RelationManagers\RoundsRelationManager;
use App\Filament\Resources\Events\RelationManagers\StandingsRelationManager;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventStanding;
use App\Models\Faction;
use App\Models\GameSystem;
use App\Models\Round;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('list page renders and shows events', function () {
    $events = Event::factory()->count(3)->create();

    Livewire::test(ListEvents::class)
        ->assertOk()
        ->assertCanSeeTableRecords($events);
});

test('can search events by name', function () {
    $matching = Event::factory()->create(['name' => 'Grand Tournament 2026']);
    $other = Event::factory()->create(['name' => 'Casual Meetup']);

    Livewire::test(ListEvents::class)
        ->searchTable('Grand Tournament 2026')
        ->assertCanSeeTableRecords(collect([$matching]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter events by status', function () {
    $draft = Event::factory()->draft()->create();
    $published = Event::factory()->published()->create();

    Livewire::test(ListEvents::class)
        ->filterTable('status', EventStatus::Draft->value)
        ->assertCanSeeTableRecords(collect([$draft]))
        ->assertCanNotSeeTableRecords(collect([$published]));
});

test('can filter events by game system', function () {
    $gameSystemA = GameSystem::factory()->create();
    $gameSystemB = GameSystem::factory()->create();
    $eventA = Event::factory()->for($gameSystemA, 'gameSystem')->create();
    $eventB = Event::factory()->for($gameSystemB, 'gameSystem')->create();

    Livewire::test(ListEvents::class)
        ->filterTable('game_system_id', $gameSystemA->id)
        ->assertCanSeeTableRecords(collect([$eventA]))
        ->assertCanNotSeeTableRecords(collect([$eventB]));
});

test('can create an event', function () {
    $gameSystem = GameSystem::factory()->create();
    $club = Club::factory()->create();
    $newEvent = Event::factory()->make();

    Livewire::test(CreateEvent::class)
        ->fillForm([
            'name' => $newEvent->name,
            'slug' => $newEvent->slug,
            'description' => $newEvent->description,
            'status' => $newEvent->status->value,
            'pairing_format' => $newEvent->pairing_format->value,
            'game_system_id' => $gameSystem->id,
            'club_id' => $club->id,
            'starts_at' => $newEvent->starts_at,
            'ends_at' => $newEvent->ends_at,
            'venue_name' => $newEvent->venue_name,
            'venue_address' => $newEvent->venue_address,
            'venue_city' => $newEvent->venue_city,
            'venue_country' => $newEvent->venue_country->value,
            'max_attendees' => $newEvent->max_attendees,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(Event::class, [
        'name' => $newEvent->name,
        'slug' => $newEvent->slug,
        'game_system_id' => $gameSystem->id,
        'club_id' => $club->id,
    ]);
});

test('can edit an event', function () {
    $event = Event::factory()->create();
    $newData = Event::factory()->make();

    Livewire::test(EditEvent::class, ['record' => $event->slug])
        ->fillForm([
            'name' => $newData->name,
            'slug' => $newData->slug,
            'status' => EventStatus::Published->value,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Event::class, [
        'id' => $event->id,
        'name' => $newData->name,
        'slug' => $newData->slug,
        'status' => EventStatus::Published->value,
    ]);
});

test('can delete an event', function () {
    $event = Event::factory()->create();

    Livewire::test(EditEvent::class, ['record' => $event->slug])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Event::class, ['id' => $event->id]);
});

test('attendees relation manager renders on edit page', function () {
    $event = Event::factory()
        ->has(EventAttendee::factory()->count(3), 'attendees')
        ->create();

    Livewire::test(AttendeesRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($event->attendees);
});

test('can create an attendee via relation manager', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();
    $faction = Faction::factory()->create();

    Livewire::test(AttendeesRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'user_id' => $user->id,
            'faction_id' => $faction->id,
        ])
        ->assertNotified();

    assertDatabaseHas(EventAttendee::class, [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'faction_id' => $faction->id,
    ]);
});

test('can edit an attendee via relation manager', function () {
    $event = Event::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->create();
    $newFaction = Faction::factory()->create();

    Livewire::test(AttendeesRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->callAction(TestAction::make(EditAction::class)->table($attendee), [
            'faction_id' => $newFaction->id,
        ])
        ->assertNotified();

    assertDatabaseHas(EventAttendee::class, [
        'id' => $attendee->id,
        'faction_id' => $newFaction->id,
    ]);
});

test('can delete an attendee via relation manager', function () {
    $event = Event::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    Livewire::test(AttendeesRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->callAction(TestAction::make(DeleteAction::class)->table($attendee))
        ->assertNotified();

    assertDatabaseMissing(EventAttendee::class, ['id' => $attendee->id]);
});

test('can search attendees by user name', function () {
    $event = Event::factory()->create();
    $matching = EventAttendee::factory()
        ->for($event)
        ->for(User::factory()->create(['name' => 'John Smith']), 'user')
        ->create();
    $other = EventAttendee::factory()
        ->for($event)
        ->for(User::factory()->create(['name' => 'Jane Doe']), 'user')
        ->create();

    Livewire::test(AttendeesRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->searchTable('John Smith')
        ->assertCanSeeTableRecords(collect([$matching]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('rounds relation manager renders on edit page', function () {
    $event = Event::factory()
        ->has(Round::factory()->count(3), 'rounds')
        ->create();

    Livewire::test(RoundsRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($event->rounds);
});

test('can create a round via relation manager', function () {
    $event = Event::factory()->create();

    Livewire::test(RoundsRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'number' => 1,
            'name' => 'Round One',
        ])
        ->assertNotified();

    assertDatabaseHas(Round::class, [
        'event_id' => $event->id,
        'number' => 1,
        'name' => 'Round One',
    ]);
});

test('can edit a round via relation manager', function () {
    $event = Event::factory()->create();
    $round = Round::factory()->for($event)->create();

    Livewire::test(RoundsRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->callAction(TestAction::make(EditAction::class)->table($round), [
            'name' => 'Updated Round',
        ])
        ->assertNotified();

    assertDatabaseHas(Round::class, [
        'id' => $round->id,
        'name' => 'Updated Round',
    ]);
});

test('can delete a round via relation manager', function () {
    $event = Event::factory()->create();
    $round = Round::factory()->for($event)->create();

    Livewire::test(RoundsRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->callAction(TestAction::make(DeleteAction::class)->table($round))
        ->assertNotified();

    assertDatabaseMissing(Round::class, ['id' => $round->id]);
});

test('standings relation manager renders on edit page', function () {
    $event = Event::factory()->create();
    $attendees = EventAttendee::factory()->count(3)->for($event)->create();
    $standings = $attendees->map(fn (EventAttendee $attendee, int $index) => EventStanding::factory()->for($event)->create([
        'event_attendee_id' => $attendee->id,
        'position' => $index + 1,
    ]));

    Livewire::test(StandingsRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($standings);
});
