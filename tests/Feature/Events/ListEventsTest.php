<?php

use App\Models\Event;
use App\Models\GameSystem;

test('it returns only publicly visible events', function () {
    Event::factory()->published()->create();
    Event::factory()->active()->create();
    Event::factory()->completed()->create();
    Event::factory()->draft()->create();
    Event::factory()->cancelled()->create();

    $this->getJson(route('events.index'))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('it is a public endpoint requiring no auth', function () {
    Event::factory()->published()->create();

    $this->getJson(route('events.index'))->assertSuccessful();
});

test('it paginates results', function () {
    Event::factory()->count(20)->published()->create();

    $this->getJson(route('events.index'))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta']);
});

test('it can be filtered by status', function () {
    Event::factory()->published()->create();
    Event::factory()->active()->create();
    Event::factory()->completed()->create();

    $this->getJson(route('events.index', ['status' => 'active']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'active');
});

test('it rejects a status filter for a non-publicly-visible status', function () {
    Event::factory()->draft()->create();

    $this->getJson(route('events.index', ['status' => 'draft']))
        ->assertUnprocessable();
});

test('it can be filtered by game_system slug', function () {
    $wh40k = GameSystem::factory()->create(['slug' => 'warhammer-40k']);
    $aos = GameSystem::factory()->create(['slug' => 'age-of-sigmar']);

    Event::factory()->published()->for($wh40k, 'gameSystem')->create();
    Event::factory()->published()->for($aos, 'gameSystem')->create();

    $this->getJson(route('events.index', ['game_system' => 'warhammer-40k']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.game_system.slug', 'warhammer-40k');
});

test('it can be searched by name', function () {
    Event::factory()->published()->create(['name' => 'London Grand Tournament']);
    Event::factory()->published()->create(['name' => 'Birmingham Open']);

    $this->getJson(route('events.index', ['search' => 'london']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'London Grand Tournament');
});

test('it rejects unknown status values', function () {
    $this->getJson(route('events.index', ['status' => 'bogus']))
        ->assertUnprocessable();
});
