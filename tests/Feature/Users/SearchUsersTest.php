<?php

use App\Models\User;

test('it searches users by username prefix', function () {
    $searcher = User::factory()->create();
    $match = User::factory()->create(['username' => 'stefano']);
    User::factory()->create(['username' => 'other']);

    $response = $this->actingAs($searcher)
        ->getJson(route('users.search', ['q' => 'stef']))
        ->assertSuccessful();

    $data = $response->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['username'])->toBe('stefano');
});

test('it searches users by name prefix', function () {
    $searcher = User::factory()->create();
    $match = User::factory()->create(['name' => 'Alice Smith', 'username' => 'alice']);
    User::factory()->create(['name' => 'Bob Jones', 'username' => 'bob']);

    $response = $this->actingAs($searcher)
        ->getJson(route('users.search', ['q' => 'Ali']))
        ->assertSuccessful();

    $data = $response->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['username'])->toBe('alice');
});

test('it excludes the authenticated user', function () {
    $searcher = User::factory()->create(['username' => 'stefano']);

    $this->actingAs($searcher)
        ->getJson(route('users.search', ['q' => 'stef']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('it returns at most 10 results', function () {
    $searcher = User::factory()->create();
    User::factory()->count(15)->sequence(fn ($seq) => ['username' => "user{$seq->index}"])->create();

    $response = $this->actingAs($searcher)
        ->getJson(route('users.search', ['q' => 'user']))
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(10);
});

test('it returns public_name, username, and id', function () {
    $searcher = User::factory()->create();
    $match = User::factory()->create(['username' => 'testuser', 'name' => 'Test User']);

    $response = $this->actingAs($searcher)
        ->getJson(route('users.search', ['q' => 'test']))
        ->assertSuccessful();

    $data = $response->json('data.0');

    expect($data)->toHaveKeys(['id', 'public_name', 'username']);
});

test('it validates q is required', function () {
    $searcher = User::factory()->create();

    $this->actingAs($searcher)
        ->getJson(route('users.search'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('q');
});
