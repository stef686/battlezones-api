<?php

use App\Models\User;

test('logging out revokes the token it was sent with', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $this->postJson(route('auth.logout'), [], ['Authorization' => 'Bearer '.$token])
        ->assertNoContent();

    auth()->forgetGuards();

    $this->getJson(route('profile'), ['Authorization' => 'Bearer '.$token])
        ->assertUnauthorized();
});

test('logging out leaves the user signed in on their other devices', function () {
    $user = User::factory()->create();
    $phone = $user->createToken('phone')->plainTextToken;
    $laptop = $user->createToken('laptop')->plainTextToken;

    $this->postJson(route('auth.logout'), [], ['Authorization' => 'Bearer '.$phone])
        ->assertNoContent();

    auth()->forgetGuards();

    $this->getJson(route('profile'), ['Authorization' => 'Bearer '.$laptop])
        ->assertSuccessful();
});

test('an unclaimed account can log out', function () {
    $user = User::factory()->unclaimed()->create();
    $token = $user->createToken('invite')->plainTextToken;

    $this->postJson(route('auth.logout'), [], ['Authorization' => 'Bearer '.$token])
        ->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});

test('logging out without a token is unauthenticated', function () {
    $this->postJson(route('auth.logout'))->assertUnauthorized();
});
