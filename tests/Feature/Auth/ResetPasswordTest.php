<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

test('a user can reset their password with a valid token', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->postJson(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Your password has been reset. You may now log in with your new password.']);

    $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
});

test('a user cannot reset their password with an invalid token', function () {
    $user = User::factory()->create();

    $this->postJson(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertJsonValidationErrors(['email']);
});

test('a user cannot reset their password without required fields', function () {
    $this->postJson(route('password.update'))
        ->assertJsonValidationErrors(['token', 'email', 'password']);
});

test('a user cannot reset their password with mismatched passwords', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->postJson(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ])
        ->assertJsonValidationErrors(['password']);
});

test('a user cannot reset their password with a password shorter than 8 characters', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->postJson(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'short',
        'password_confirmation' => 'short',
    ])
        ->assertJsonValidationErrors(['password']);
});

test('after a password reset the old password no longer works', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->postJson(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertOk();

    $this->postJson(route('login.token'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test Device',
    ])->assertJsonValidationErrors('email');
});

test('after a password reset the new password works for login', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->postJson(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertOk();

    $this->postJson(route('login.token'), [
        'email' => $user->email,
        'password' => 'new-password',
        'device_name' => 'Test Device',
    ])
        ->assertOk()
        ->assertJsonStructure(['token']);
});
