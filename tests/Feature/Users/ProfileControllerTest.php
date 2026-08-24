<?php

use App\Models\User;
use App\Notifications\Events\RoundLiveNotification;

test('current user can load profile data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('profile'))
        ->assertOk()
        ->assertJson([
            'data' => [
                'public_name' => $user->name,
            ],
        ]);
});

test('a user can load another user\'s profile data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $profileUser = User::factory()->create();

    $this->get(route('profile.show', $profileUser))
        ->assertOk()
        ->assertJson([
            'data' => [
                'public_name' => $profileUser->name,
            ],
        ]);
});

test('profile followers and following counts reflect actual data', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    $following = User::factory()->create();
    $follower->following()->attach($user);
    $user->following()->attach($following);
    $this->actingAs($user);

    $this->get(route('profile'))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'followers_count' => 1,
                'following_count' => 1,
            ],
        ]);
});

test('is_following is present on another user\'s profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $user->following()->attach($other);
    $this->actingAs($user);

    $this->get(route('profile.show', $other))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'is_following' => true,
            ],
        ]);
});

test('is_following is absent on own profile', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('profile'))
        ->assertSuccessful()
        ->assertJsonMissing(['is_following']);
});

test('the current user sees their claim and verification state', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('profile'))
        ->assertSuccessful()
        ->assertJsonPath('data.is_claimed', true)
        ->assertJsonPath('data.email_verified', true)
        ->assertJsonPath('data.unread_notifications_count', 0);
});

test('an unclaimed account reads as unclaimed, so the SPA can restrict it in one field', function () {
    $user = User::factory()->unclaimed()->unverified()->create();

    $this->actingAs($user)
        ->getJson(route('profile'))
        ->assertSuccessful()
        ->assertJsonPath('data.is_claimed', false)
        ->assertJsonPath('data.email_verified', false);
});

test('the unread notification count reflects unread notifications', function () {
    $user = User::factory()->create();

    [, $game] = submittedGame($user);

    $user->notify(new RoundLiveNotification($game));

    $this->actingAs($user)
        ->getJson(route('profile'))
        ->assertSuccessful()
        ->assertJsonPath('data.unread_notifications_count', 1);

    $user->unreadNotifications->markAsRead();

    $this->actingAs($user)
        ->getJson(route('profile'))
        ->assertJsonPath('data.unread_notifications_count', 0);
});

test('claim state stays off another user profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('profile.show', $other))
        ->assertSuccessful()
        ->assertJsonMissingPath('data.is_claimed')
        ->assertJsonMissingPath('data.unread_notifications_count');
});
