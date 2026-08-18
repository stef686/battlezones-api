<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('an unclaimed account has no password and is not claimed', function () {
    $user = User::factory()->unclaimed()->create();

    expect($user->password)->toBeNull()
        ->and($user->isClaimed())->toBeFalse();
});

test('an unclaimed account cannot log in', function () {
    $user = User::factory()->unclaimed()->create();

    $this->postJson(route('login.token'), [
        'email' => $user->email,
        'password' => '',
        'device_name' => 'iPhone',
    ])->assertUnprocessable();
});

test('an unclaimed account cannot be logged into with any password', function () {
    $user = User::factory()->unclaimed()->create();

    $this->postJson(route('login.token'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'iPhone',
    ])->assertUnprocessable();
});

test('a registered account is claimed', function () {
    $user = User::factory()->create();

    expect($user->isClaimed())->toBeTrue();
});

test('an unclaimed account is rejected before any hash comparison', function () {
    $user = User::factory()->unclaimed()->create();

    Hash::shouldReceive('check')->never();

    $this->postJson(route('login.token'), [
        'email' => $user->email,
        'password' => 'anything',
        'device_name' => 'iPhone',
    ])->assertUnprocessable();
});
