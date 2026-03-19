<?php

use App\Enums\Country;
use App\Models\User;

test('a user can update their name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['name' => 'New Name'])
        ->assertSuccessful()
        ->assertJsonPath('data.public_name', 'New Name');

    expect($user->fresh()->name)->toBe('New Name');
});

test('a user can update their username', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['username' => 'newuser123'])
        ->assertSuccessful();

    expect($user->fresh()->username)->toBe('newuser123');
});

test('a user can update their country', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['country' => 'US'])
        ->assertSuccessful()
        ->assertJsonPath('data.country', 'US');

    expect($user->fresh()->country)->toBe(Country::UnitedStates);
});

test('a user can set country to null', function () {
    $user = User::factory()->create(['country' => Country::UnitedStates]);

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['country' => null])
        ->assertSuccessful();

    expect($user->fresh()->country)->toBeNull();
});

test('a user can update show_public_name', function () {
    $user = User::factory()->create(['show_public_name' => true]);

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['show_public_name' => false])
        ->assertSuccessful();

    expect($user->fresh()->show_public_name)->toBeFalse();
});

test('public_name returns username when show_public_name is false', function () {
    $user = User::factory()->create([
        'name' => 'Real Name',
        'username' => 'hiddenuser',
        'show_public_name' => false,
    ]);

    expect($user->public_name)->toBe('hiddenuser');
});

test('public_name returns name when show_public_name is true', function () {
    $user = User::factory()->create([
        'name' => 'Real Name',
        'username' => 'hiddenuser',
        'show_public_name' => true,
    ]);

    expect($user->public_name)->toBe('Real Name');
});

test('username must match the required format', function (string $username) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['username' => $username])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
})->with([
    'starts with number' => '1badname',
    'starts with underscore' => '_badname',
    'too short' => 'ab',
    'too long' => str_repeat('a', 31),
    'contains spaces' => 'bad name',
    'contains special chars' => 'bad@name',
]);

test('username must be unique', function () {
    User::factory()->create(['username' => 'taken']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['username' => 'taken'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

test('a user can keep their own username', function () {
    $user = User::factory()->create(['username' => 'myname']);

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['username' => 'myname'])
        ->assertSuccessful();
});

test('country rejects invalid codes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('profile.update'), ['country' => 'XX'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('country');
});
