<?php

use App\Filament\Widgets\StatsOverviewWidget;
use App\Models\Club;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('it displays correct stats', function () {
    User::factory()->count(3)->create();
    Club::factory()->count(2)->create();
    Event::factory()->count(4)->create([
        'starts_at' => now()->subMonth(),
    ]);
    Event::factory()->count(2)->create([
        'starts_at' => now()->addDays(3),
    ]);

    Livewire::test(StatsOverviewWidget::class)
        ->assertSeeText('4') // admin + 3 users
        ->assertSeeText('2') // clubs
        ->assertSeeText('6') // total events
        ->assertSeeText('2'); // events this month
});
