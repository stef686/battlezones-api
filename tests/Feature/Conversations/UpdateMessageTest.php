<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

test('it updates a message within the 15-minute window', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->patchJson(route('conversations.messages.update', [$conversation, $message]), [
            'body' => 'Updated body',
        ])
        ->assertSuccessful();

    expect($response->json('data.body'))->toBe('Updated body')
        ->and($response->json('data.is_edited'))->toBeTrue();
});

test('it returns 403 after 15 minutes', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
    ]);

    $this->travel(16)->minutes();

    $this->actingAs($user)
        ->patchJson(route('conversations.messages.update', [$conversation, $message]), [
            'body' => 'Too late',
        ])
        ->assertForbidden();
});

test('it returns 403 for non-owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($user)
        ->patchJson(route('conversations.messages.update', [$conversation, $message]), [
            'body' => 'Not mine',
        ])
        ->assertForbidden();
});

test('it cannot edit a deleted message', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $message = Message::factory()->deleted()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->patchJson(route('conversations.messages.update', [$conversation, $message]), [
            'body' => 'Revive',
        ])
        ->assertForbidden();
});
