<?php

use App\Enums\ConversationTab;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Queries\ConversationListQuery;

test('forUser returns conversations the user participates in', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Conversation::factory()->withUsers($otherUser, $stranger)->create();

    $result = ConversationListQuery::forUser($user->id)->toQuery()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($conversation->id);
});

test('forUser excludes deleted conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

    $result = ConversationListQuery::forUser($user->id)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('forUser excludes archived conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    $result = ConversationListQuery::forUser($user->id)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('primary tab includes group conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Conversation::factory()->withUsers($user, $otherUser)->group('Test Group')->create();

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result)->toHaveCount(1);
});

test('primary tab includes conversations user started', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result)->toHaveCount(1);
});

test('primary tab includes conversations with followed users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->following()->attach($otherUser);

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result)->toHaveCount(1);
});

test('primary tab excludes event conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Conversation::factory()->withUsers($user, $otherUser)->create(['event_id' => 1]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('primary tab excludes stranger-initiated conversations', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $stranger->id]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('events tab returns only event conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $eventConv = Conversation::factory()->withUsers($user, $otherUser)->create(['event_id' => 1]);
    Conversation::factory()->withUsers($user, $otherUser)->create();

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Events)->toQuery()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($eventConv->id);
});

test('requests tab returns stranger-initiated non-event 1-on-1 conversations', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $stranger->id]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Requests)->toQuery()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($conversation->id);
});

test('requests tab excludes followed user conversations', function () {
    $user = User::factory()->create();
    $followed = User::factory()->create();

    $user->following()->attach($followed);

    $conversation = Conversation::factory()->withUsers($user, $followed)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $followed->id]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Requests)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('requests tab excludes event conversations', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create(['event_id' => 1]);
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $stranger->id]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Requests)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('archived tab returns archived conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    Conversation::factory()->withUsers($user, $otherUser)->create();

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Archived)->toQuery()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($conversation->id);
});

test('archived tab excludes deleted conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    $conversation->users()->updateExistingPivot($user->id, [
        'archived_at' => now(),
        'deleted_at' => now(),
    ]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Archived)->toQuery()->get();

    expect($result)->toHaveCount(0);
});
