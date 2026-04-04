<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

test('a valid token can be refreshed for a new one', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device');

    $this->postJson(route('auth.refresh'), [], [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['token', 'expires_at']);

    // Old token should be revoked
    expect(PersonalAccessToken::findToken($token->plainTextToken))->toBeNull();

    // User should have exactly one token (the new one)
    expect($user->tokens()->count())->toBe(1);
});

test('a recently expired token within grace period can be refreshed', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device');

    // Expire the token by 1 minute (within 2-minute grace period)
    $expiration = config('sanctum.expiration');
    PersonalAccessToken::where('id', $token->accessToken->id)
        ->update(['created_at' => now()->subMinutes($expiration + 1)]);

    $this->postJson(route('auth.refresh'), [], [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['token', 'expires_at']);
});

test('a token expired beyond grace period returns 401', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device');

    // Expire the token by 5 minutes (beyond 2-minute grace period)
    $expiration = config('sanctum.expiration');
    PersonalAccessToken::where('id', $token->accessToken->id)
        ->update(['created_at' => now()->subMinutes($expiration + 5)]);

    $this->postJson(route('auth.refresh'), [], [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])->assertUnauthorized();
});

test('a revoked token cannot be refreshed', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device');

    // Revoke the token (simulate logout)
    $user->tokens()->delete();

    $this->postJson(route('auth.refresh'), [], [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])->assertUnauthorized();
});

test('a request without a bearer token returns 401', function () {
    $this->postJson(route('auth.refresh'))
        ->assertUnauthorized();
});

test('the new token preserves the device name', function () {
    $user = User::factory()->create();
    $token = $user->createToken('My iPhone');

    $response = $this->postJson(route('auth.refresh'), [], [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])->assertSuccessful();

    $newAccessToken = PersonalAccessToken::findToken($response->json('token'));
    expect($newAccessToken->name)->toBe('My iPhone');
});

test('the refresh endpoint is rate limited', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $token = $user->createToken('device');

        $this->postJson(route('auth.refresh'), [], [
            'Authorization' => 'Bearer '.$token->plainTextToken,
        ]);
    }

    $token = $user->createToken('device');

    $this->postJson(route('auth.refresh'), [], [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])->assertTooManyRequests();
});

test('login response includes expires_at', function () {
    $user = User::factory()->create();

    $this->postJson(route('login.token'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test Device',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['token', 'expires_at']);
});
