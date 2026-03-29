<?php

use App\Models\Conversation;
use App\Models\User;

test('it archives a conversation for the requesting user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    $this->actingAs($user)
        ->postJson(route('conversations.archive', $conversation))
        ->assertSuccessful()
        ->assertJson(['message' => 'Conversation archived.']);

    $pivot = $conversation->users()->where('user_id', $user->id)->first()->pivot;
    expect($pivot->archived_at)->not->toBeNull();

    $otherPivot = $conversation->users()->where('user_id', $otherUser->id)->first()->pivot;
    expect($otherPivot->archived_at)->toBeNull();
});

test('it returns 403 for non-participant', function () {
    $user = User::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($userA, $userB)->create();

    $this->actingAs($user)
        ->postJson(route('conversations.archive', $conversation))
        ->assertForbidden();
});

test('it returns 403 for user who deleted the conversation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

    $this->actingAs($user)
        ->postJson(route('conversations.archive', $conversation))
        ->assertForbidden();
});

test('re-archiving an already archived conversation is idempotent', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()->subDay()]);

    $this->actingAs($user)
        ->postJson(route('conversations.archive', $conversation))
        ->assertSuccessful();

    $pivot = $conversation->users()->where('user_id', $user->id)->first()->pivot;
    expect($pivot->archived_at)->not->toBeNull();
});
