<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

test('an expired token returns 401', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test-device');

    PersonalAccessToken::where('id', $token->accessToken->id)
        ->update(['created_at' => now()->subDays(31)]);

    $this->getJson(route('profile'), [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])->assertUnauthorized();
});

test('a valid token can access protected routes', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test-device');

    $this->getJson(route('profile'), [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])->assertSuccessful();
});
