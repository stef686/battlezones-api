<?php

use App\Models\Conversation;
use App\Models\User;

test('it unarchives an archived conversation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    $this->actingAs($user)
        ->postJson(route('conversations.unarchive', $conversation))
        ->assertSuccessful();

    $pivot = $conversation->users()->where('user_id', $user->id)->first()->pivot;
    expect($pivot->archived_at)->toBeNull();
});

test('it returns 403 for non-participant', function () {
    $user = User::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($userA, $userB)->create();

    $this->actingAs($user)
        ->postJson(route('conversations.unarchive', $conversation))
        ->assertForbidden();
});

test('it returns 403 when conversation is not archived', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    $this->actingAs($user)
        ->postJson(route('conversations.unarchive', $conversation))
        ->assertForbidden();
});
