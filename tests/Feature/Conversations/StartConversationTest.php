<?php

use App\Models\Conversation;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;
use Illuminate\Support\Facades\Notification;

test('it creates a conversation and returns resource', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_id' => $recipient->id,
            'body' => 'Hello there!',
        ])
        ->assertSuccessful();

    $data = $response->json('data');

    expect($data['participant']['id'])->toBe($recipient->id)
        ->and($data['latest_message']['body'])->toBe('Hello there!');

    $this->assertDatabaseCount('conversations', 1);
    $this->assertDatabaseCount('messages', 1);
});

test('it reuses an existing conversation between the same users', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($sender, $recipient)->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_id' => $recipient->id,
            'body' => 'Another message',
        ])
        ->assertSuccessful();

    $this->assertDatabaseCount('conversations', 1);
    $this->assertDatabaseCount('messages', 1);
});

test('it resurfaces a deleted conversation for both users', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($sender, $recipient)->create();
    $conversation->users()->updateExistingPivot($sender->id, ['deleted_at' => now()]);
    $conversation->users()->updateExistingPivot($recipient->id, ['deleted_at' => now()]);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_id' => $recipient->id,
            'body' => 'I am back',
        ])
        ->assertSuccessful();

    $pivot = $conversation->users()->where('user_id', $sender->id)->first()->pivot;
    expect($pivot->deleted_at)->toBeNull();

    $recipientPivot = $conversation->users()->where('user_id', $recipient->id)->first()->pivot;
    expect($recipientPivot->deleted_at)->toBeNull();
});

test('it cannot message self', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('conversations.store'), [
            'recipient_id' => $user->id,
            'body' => 'Hello me',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_id');
});

test('it validates required fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('conversations.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['recipient_id', 'body']);
});

test('it notifies the recipient', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_id' => $recipient->id,
            'body' => 'Hey!',
        ])
        ->assertSuccessful();

    Notification::assertSentTo($recipient, NewMessageNotification::class);
});
