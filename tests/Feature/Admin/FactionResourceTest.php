<?php

use App\Filament\Resources\Factions\Pages\CreateFaction;
use App\Filament\Resources\Factions\Pages\EditFaction;
use App\Filament\Resources\Factions\Pages\ListFactions;
use App\Models\Faction;
use App\Models\GameSystem;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('list page renders and shows factions', function () {
    $factions = Faction::factory()->count(3)->create();

    Livewire::test(ListFactions::class)
        ->assertOk()
        ->assertCanSeeTableRecords($factions);
});

test('can search factions by name', function () {
    $matching = Faction::factory()->create(['name' => 'Space Marines']);
    $other = Faction::factory()->create(['name' => 'Orks']);

    Livewire::test(ListFactions::class)
        ->searchTable('Space Marines')
        ->assertCanSeeTableRecords(collect([$matching]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter factions by game system', function () {
    $gameSystemA = GameSystem::factory()->create();
    $gameSystemB = GameSystem::factory()->create();
    $factionA = Faction::factory()->for($gameSystemA, 'gameSystem')->create();
    $factionB = Faction::factory()->for($gameSystemB, 'gameSystem')->create();

    Livewire::test(ListFactions::class)
        ->filterTable('game_system_id', $gameSystemA->id)
        ->assertCanSeeTableRecords(collect([$factionA]))
        ->assertCanNotSeeTableRecords(collect([$factionB]));
});

test('can create a faction', function () {
    $gameSystem = GameSystem::factory()->create();
    $newFaction = Faction::factory()->make();

    Livewire::test(CreateFaction::class)
        ->fillForm([
            'game_system_id' => $gameSystem->id,
            'name' => $newFaction->name,
            'slug' => $newFaction->slug,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(Faction::class, [
        'game_system_id' => $gameSystem->id,
        'name' => $newFaction->name,
        'slug' => $newFaction->slug,
    ]);
});

test('can edit a faction', function () {
    $faction = Faction::factory()->create();
    $newGameSystem = GameSystem::factory()->create();
    $newData = Faction::factory()->make();

    Livewire::test(EditFaction::class, ['record' => $faction->id])
        ->fillForm([
            'game_system_id' => $newGameSystem->id,
            'name' => $newData->name,
            'slug' => $newData->slug,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Faction::class, [
        'id' => $faction->id,
        'game_system_id' => $newGameSystem->id,
        'name' => $newData->name,
        'slug' => $newData->slug,
    ]);
});

test('can delete a faction', function () {
    $faction = Faction::factory()->create();

    Livewire::test(EditFaction::class, ['record' => $faction->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Faction::class, ['id' => $faction->id]);
});
