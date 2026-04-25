<?php

use App\Filament\Resources\GameSystems\Pages\CreateGameSystem;
use App\Filament\Resources\GameSystems\Pages\EditGameSystem;
use App\Filament\Resources\GameSystems\Pages\ListGameSystems;
use App\Filament\Resources\GameSystems\RelationManagers\FactionsRelationManager;
use App\Models\Faction;
use App\Models\GameSystem;
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

test('list page renders and shows game systems', function () {
    $gameSystems = GameSystem::factory()->count(3)->create();

    Livewire::test(ListGameSystems::class)
        ->assertOk()
        ->assertCanSeeTableRecords($gameSystems);
});

test('can search game systems by name', function () {
    $matching = GameSystem::factory()->create(['name' => 'Warhammer 40k']);
    $other = GameSystem::factory()->create(['name' => 'Age of Sigmar']);

    Livewire::test(ListGameSystems::class)
        ->searchTable('Warhammer 40k')
        ->assertCanSeeTableRecords(collect([$matching]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can create a game system', function () {
    $newGameSystem = GameSystem::factory()->make();

    Livewire::test(CreateGameSystem::class)
        ->fillForm([
            'name' => $newGameSystem->name,
            'slug' => $newGameSystem->slug,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(GameSystem::class, [
        'name' => $newGameSystem->name,
        'slug' => $newGameSystem->slug,
    ]);
});

test('can edit a game system', function () {
    $gameSystem = GameSystem::factory()->create();
    $newData = GameSystem::factory()->make();

    Livewire::test(EditGameSystem::class, ['record' => $gameSystem->slug])
        ->fillForm([
            'name' => $newData->name,
            'slug' => $newData->slug,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(GameSystem::class, [
        'id' => $gameSystem->id,
        'name' => $newData->name,
        'slug' => $newData->slug,
    ]);
});

test('can delete a game system', function () {
    $gameSystem = GameSystem::factory()->create();

    Livewire::test(EditGameSystem::class, ['record' => $gameSystem->slug])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(GameSystem::class, ['id' => $gameSystem->id]);
});

test('factions relation manager renders on edit page', function () {
    $gameSystem = GameSystem::factory()
        ->has(Faction::factory()->count(3))
        ->create();

    Livewire::test(FactionsRelationManager::class, [
        'ownerRecord' => $gameSystem,
        'pageClass' => EditGameSystem::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($gameSystem->factions);
});

test('can create a faction via relation manager', function () {
    $gameSystem = GameSystem::factory()->create();
    $newFaction = Faction::factory()->make();

    Livewire::test(FactionsRelationManager::class, [
        'ownerRecord' => $gameSystem,
        'pageClass' => EditGameSystem::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'name' => $newFaction->name,
            'slug' => $newFaction->slug,
        ])
        ->assertNotified();

    assertDatabaseHas(Faction::class, [
        'game_system_id' => $gameSystem->id,
        'name' => $newFaction->name,
        'slug' => $newFaction->slug,
    ]);
});

test('can edit a faction via relation manager', function () {
    $gameSystem = GameSystem::factory()->create();
    $faction = Faction::factory()->for($gameSystem, 'gameSystem')->create();
    $newData = Faction::factory()->make();

    Livewire::test(FactionsRelationManager::class, [
        'ownerRecord' => $gameSystem,
        'pageClass' => EditGameSystem::class,
    ])
        ->callAction(TestAction::make(EditAction::class)->table($faction), [
            'name' => $newData->name,
            'slug' => $newData->slug,
        ])
        ->assertNotified();

    assertDatabaseHas(Faction::class, [
        'id' => $faction->id,
        'name' => $newData->name,
        'slug' => $newData->slug,
    ]);
});

test('can delete a faction via relation manager', function () {
    $gameSystem = GameSystem::factory()->create();
    $faction = Faction::factory()->for($gameSystem, 'gameSystem')->create();

    Livewire::test(FactionsRelationManager::class, [
        'ownerRecord' => $gameSystem,
        'pageClass' => EditGameSystem::class,
    ])
        ->callAction(TestAction::make(DeleteAction::class)->table($faction))
        ->assertNotified();

    assertDatabaseMissing(Faction::class, ['id' => $faction->id]);
});
