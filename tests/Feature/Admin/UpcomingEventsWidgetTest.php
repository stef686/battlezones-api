<?php

use App\Filament\Widgets\UpcomingEventsWidget;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('it shows the next 5 upcoming events', function () {
    Event::factory()->count(2)->create([
        'starts_at' => now()->subWeek(),
    ]);

    $upcoming = Event::factory()->count(6)->create([
        'starts_at' => now()->addWeek(),
    ]);

    $component = Livewire::test(UpcomingEventsWidget::class);

    $upcoming->sortBy('starts_at')->take(5)->each(function (Event $event) use ($component) {
        $component->assertSeeText($event->name);
    });
});

test('it does not show past events', function () {
    $pastEvent = Event::factory()->create([
        'starts_at' => now()->subDay(),
    ]);

    Livewire::test(UpcomingEventsWidget::class)
        ->assertDontSeeText($pastEvent->name);
});
