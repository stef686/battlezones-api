<?php

use App\Filament\Resources\Clubs\Pages\CreateClub;
use App\Filament\Resources\Clubs\Pages\EditClub;
use App\Filament\Resources\Clubs\Pages\ListClubs;
use App\Filament\Resources\Clubs\RelationManagers\MembersRelationManager;
use App\Models\Club;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('list page renders and shows clubs', function () {
    $clubs = Club::factory()->count(3)->create();

    Livewire::test(ListClubs::class)
        ->assertOk()
        ->assertCanSeeTableRecords($clubs);
});

test('can search clubs by name', function () {
    $matching = Club::factory()->create(['name' => 'Thunder FC']);
    $other = Club::factory()->create(['name' => 'Lightning United']);

    Livewire::test(ListClubs::class)
        ->searchTable('Thunder FC')
        ->assertCanSeeTableRecords(collect([$matching]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can search clubs by city', function () {
    $matching = Club::factory()->create(['city' => 'Manchester']);
    $other = Club::factory()->create(['city' => 'London']);

    Livewire::test(ListClubs::class)
        ->searchTable('Manchester')
        ->assertCanSeeTableRecords(collect([$matching]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can create a club', function () {
    $newClub = Club::factory()->make();

    Livewire::test(CreateClub::class)
        ->fillForm([
            'name' => $newClub->name,
            'slug' => $newClub->slug,
            'city' => $newClub->city,
            'country' => $newClub->country->value,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(Club::class, [
        'name' => $newClub->name,
        'slug' => $newClub->slug,
        'city' => $newClub->city,
    ]);
});

test('can edit a club', function () {
    $club = Club::factory()->create();
    $newData = Club::factory()->make();

    Livewire::test(EditClub::class, ['record' => $club->slug])
        ->fillForm([
            'name' => $newData->name,
            'slug' => $newData->slug,
            'city' => $newData->city,
            'country' => $newData->country->value,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Club::class, [
        'id' => $club->id,
        'name' => $newData->name,
        'slug' => $newData->slug,
    ]);
});

test('can delete a club', function () {
    $club = Club::factory()->create();

    Livewire::test(EditClub::class, ['record' => $club->slug])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Club::class, ['id' => $club->id]);
});

test('members relation manager renders on edit page', function () {
    $club = Club::factory()
        ->hasAttached(User::factory()->count(3), [], 'members')
        ->create();

    Livewire::test(MembersRelationManager::class, [
        'ownerRecord' => $club,
        'pageClass' => EditClub::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($club->members);
});

test('can attach a member via relation manager', function () {
    $club = Club::factory()->create();
    $user = User::factory()->create();

    Livewire::test(MembersRelationManager::class, [
        'ownerRecord' => $club,
        'pageClass' => EditClub::class,
    ])
        ->callAction(TestAction::make(AttachAction::class)->table(), [
            'recordId' => $user->id,
        ])
        ->assertNotified();

    expect($club->members()->where('users.id', $user->id)->exists())->toBeTrue();
});

test('can detach a member via relation manager', function () {
    $user = User::factory()->create();
    $club = Club::factory()
        ->hasAttached($user, [], 'members')
        ->create();

    Livewire::test(MembersRelationManager::class, [
        'ownerRecord' => $club,
        'pageClass' => EditClub::class,
    ])
        ->callAction(TestAction::make(DetachAction::class)->table($user))
        ->assertNotified();

    expect($club->members()->where('users.id', $user->id)->exists())->toBeFalse();
});
