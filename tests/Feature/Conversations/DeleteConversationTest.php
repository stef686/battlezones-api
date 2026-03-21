<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('it sets pivot deleted_at for the requesting user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    $this->actingAs($user)
        ->deleteJson(route('conversations.destroy', $conversation))
        ->assertSuccessful();

    $pivot = $conversation->users()->where('user_id', $user->id)->first()->pivot;
    expect($pivot->deleted_at)->not->toBeNull();
});

test('other user still sees the conversation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);

    $this->actingAs($user)
        ->deleteJson(route('conversations.destroy', $conversation))
        ->assertSuccessful();

    $this->actingAs($otherUser)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('deleted conversation no longer appears in list', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->deleteJson(route('conversations.destroy', $conversation))
        ->assertSuccessful();

    $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('new message after delete resurfaces conversation', function () {
    Notification::fake();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    // User deletes conversation
    $this->actingAs($user)
        ->deleteJson(route('conversations.destroy', $conversation))
        ->assertSuccessful();

    // Other user sends a message — this resurfaces conversation for deleted user
    $this->actingAs($otherUser)
        ->postJson(route('conversations.messages.store', $conversation), [
            'body' => 'New message!',
        ])
        ->assertSuccessful();

    // User's pivot deleted_at should now be null (resurfaced)
    $pivot = $conversation->users()->where('user_id', $user->id)->first()->pivot;
    expect($pivot->deleted_at)->toBeNull();

    // User can now see the conversation again
    $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it returns 403 for non-participant', function () {
    $user = User::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($userA, $userB)->create();

    $this->actingAs($user)
        ->deleteJson(route('conversations.destroy', $conversation))
        ->assertForbidden();
});
