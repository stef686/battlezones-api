<?php

use App\Models\User;

test('a user can request an api login token', function () {
    $user = User::factory()->create();

    $this->postJson(route('login.token'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test Device',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['token']);
});

test('a user cannot request an api login token with incorrect data', function () {
    $user = User::factory()->create();

    $this->postJson(route('login.token'), [
        'email' => $user->email,
        'password' => 'incorrect-password',
        'device_name' => 'Test Device',
    ])
        ->assertJsonValidationErrors('email');
});
