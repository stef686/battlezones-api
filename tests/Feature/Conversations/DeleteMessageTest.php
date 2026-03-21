<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

test('it tombstones the message', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('conversations.messages.destroy', [$conversation, $message]))
        ->assertSuccessful();

    $message->refresh();
    expect($message->body)->toBeNull()
        ->and($message->deleted_at)->not->toBeNull();
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
        ->deleteJson(route('conversations.messages.destroy', [$conversation, $message]))
        ->assertForbidden();
});

test('it cannot delete an already deleted message', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $message = Message::factory()->deleted()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('conversations.messages.destroy', [$conversation, $message]))
        ->assertForbidden();
});
