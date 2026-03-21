<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

test('it returns messages for a participant', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->count(3)->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.show', $conversation))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('it returns 403 for non-participant', function () {
    $user = User::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($userA, $userB)->create();

    $this->actingAs($user)
        ->getJson(route('conversations.show', $conversation))
        ->assertForbidden();
});

test('it returns 403 for user who deleted the conversation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

    $this->actingAs($user)
        ->getJson(route('conversations.show', $conversation))
        ->assertForbidden();
});

test('it paginates messages', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->count(20)->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.show', $conversation))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta']);
});

test('archived conversation is still viewable', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    $this->actingAs($user)
        ->getJson(route('conversations.show', $conversation))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it updates last_read_at when viewing', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.show', $conversation))
        ->assertSuccessful();

    $pivot = $conversation->users()->where('user_id', $user->id)->first()->pivot;
    expect($pivot->last_read_at)->not->toBeNull();
});
