<?php

use App\Models\Conversation;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;
use Illuminate\Support\Facades\Notification;

test('it sends a message in a conversation', function () {
    Notification::fake();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    $response = $this->actingAs($user)
        ->postJson(route('conversations.messages.store', $conversation), [
            'body' => 'Hello!',
        ])
        ->assertSuccessful();

    expect($response->json('data.body'))->toBe('Hello!');
    $this->assertDatabaseCount('messages', 1);
});

test('it resurfaces conversation for deleted recipient', function () {
    Notification::fake();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($otherUser->id, ['deleted_at' => now()]);

    $this->actingAs($user)
        ->postJson(route('conversations.messages.store', $conversation), [
            'body' => 'Come back!',
        ])
        ->assertSuccessful();

    $pivot = $conversation->users()->where('user_id', $otherUser->id)->first()->pivot;
    expect($pivot->deleted_at)->toBeNull();
});

test('it returns 403 for non-participant', function () {
    $user = User::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($userA, $userB)->create();

    $this->actingAs($user)
        ->postJson(route('conversations.messages.store', $conversation), [
            'body' => 'Sneaky',
        ])
        ->assertForbidden();
});

test('it validates body', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    $this->actingAs($user)
        ->postJson(route('conversations.messages.store', $conversation), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('body');
});

test('it notifies the recipient', function () {
    Notification::fake();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    $this->actingAs($user)
        ->postJson(route('conversations.messages.store', $conversation), [
            'body' => 'Notification test',
        ])
        ->assertSuccessful();

    Notification::assertSentTo($otherUser, NewMessageNotification::class);
});
