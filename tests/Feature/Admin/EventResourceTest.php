<?php

use App\Enums\EventStatus;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Models\Club;
use App\Models\Event;
use App\Models\GameSystem;
use App\Models\User;
use Filament\Actions\DeleteAction;
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
