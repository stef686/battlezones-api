<?php

use App\Enums\Country;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('list page renders and shows users', function () {
    $users = User::factory()->count(3)->create();

    Livewire::test(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords($users);
});

test('can search users by name', function () {
    $matchingUser = User::factory()->create(['name' => 'John Smith']);
    $otherUser = User::factory()->create(['name' => 'Jane Doe']);

    Livewire::test(ListUsers::class)
        ->searchTable('John Smith')
        ->assertCanSeeTableRecords(collect([$matchingUser]))
        ->assertCanNotSeeTableRecords(collect([$otherUser]));
});

test('can search users by email', function () {
    $matchingUser = User::factory()->create(['email' => 'findme@example.com']);
    $otherUser = User::factory()->create(['email' => 'other@example.com']);

    Livewire::test(ListUsers::class)
        ->searchTable('findme@example.com')
        ->assertCanSeeTableRecords(collect([$matchingUser]))
        ->assertCanNotSeeTableRecords(collect([$otherUser]));
});

test('can search users by username', function () {
    $matchingUser = User::factory()->create(['username' => 'uniquehandle']);
    $otherUser = User::factory()->create(['username' => 'differentone']);

    Livewire::test(ListUsers::class)
        ->searchTable('uniquehandle')
        ->assertCanSeeTableRecords(collect([$matchingUser]))
        ->assertCanNotSeeTableRecords(collect([$otherUser]));
});

test('can filter users by country', function () {
    $ukUser = User::factory()->create(['country' => Country::UnitedKingdom]);
    $usUser = User::factory()->create(['country' => Country::UnitedStates]);

    Livewire::test(ListUsers::class)
        ->filterTable('country', Country::UnitedKingdom->value)
        ->assertCanSeeTableRecords(collect([$ukUser]))
        ->assertCanNotSeeTableRecords(collect([$usUser]));
});

test('can filter users by verified status', function () {
    $verified = User::factory()->create();
    $unverified = User::factory()->unverified()->create();

    Livewire::test(ListUsers::class)
        ->filterTable('email_verified_at', true)
        ->assertCanSeeTableRecords(collect([$verified]))
        ->assertCanNotSeeTableRecords(collect([$unverified]));
});

test('can create a user', function () {
    $newUser = User::factory()->make();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => $newUser->name,
            'username' => $newUser->username,
            'email' => $newUser->email,
            'password' => 'password123',
            'country' => $newUser->country->value,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => $newUser->name,
        'email' => $newUser->email,
        'username' => $newUser->username,
    ]);
});

test('can edit a user', function () {
    $user = User::factory()->create();
    $newData = User::factory()->make();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            'username' => $newData->username,
            'country' => $newData->country->value,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'name' => $newData->name,
        'email' => $newData->email,
    ]);
});

test('can delete a user', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(User::class, ['id' => $user->id]);
});
