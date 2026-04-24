<?php

use App\Enums\ConversationTab;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Message;
use App\Models\User;
use App\Queries\ConversationListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

test('forUser returns conversations the user participates in', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->group('Test')->create();
    Conversation::factory()->withUsers($otherUser, $stranger)->group('Other')->create();

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($conversation->id);
});

test('forUser excludes deleted conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->group('Test')->create();
    $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('forUser excludes archived conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->group('Test')->create();
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

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

    Conversation::factory()->withUsers($user, $otherUser)->create(['event_id' => Event::factory()->create()->id]);

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

    $eventConv = Conversation::factory()->withUsers($user, $otherUser)->create(['event_id' => Event::factory()->create()->id]);
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

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create(['event_id' => Event::factory()->create()->id]);
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

test('deleted conversations excluded from primary tab', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);
    $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('deleted conversations excluded from events tab', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create(['event_id' => Event::factory()->create()->id]);
    $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Events)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('deleted conversations excluded from requests tab', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $stranger->id]);
    $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Requests)->toQuery()->get();

    expect($result)->toHaveCount(0);
});

test('unread count is zero when no messages exist', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Conversation::factory()->withUsers($user, $otherUser)->group('Test')->create();

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->first();

    expect((int) $result->unread_count)->toBe(0);
});

test('unread count is zero when all messages are from auth user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->count(3)->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->first();

    expect((int) $result->unread_count)->toBe(0);
});

test('unread count counts messages from other users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'created_at' => now()->subMinutes(10),
    ]);
    Message::factory()->count(3)->create([
        'conversation_id' => $conversation->id,
        'user_id' => $otherUser->id,
    ]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->first();

    expect((int) $result->unread_count)->toBe(3);
});

test('unread count respects last_read_at', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'created_at' => now()->subHours(3),
    ]);
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $otherUser->id,
        'created_at' => now()->subHours(2),
    ]);

    $conversation->users()->updateExistingPivot($user->id, ['last_read_at' => now()->subHour()]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $otherUser->id,
        'created_at' => now(),
    ]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->first();

    expect((int) $result->unread_count)->toBe(1);
});

test('null last_read_at counts all other-user messages as unread', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->group('Test')->create();
    Message::factory()->count(5)->create([
        'conversation_id' => $conversation->id,
        'user_id' => $otherUser->id,
    ]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->first();

    expect((int) $result->unread_count)->toBe(5);
});

test('results ordered by latest message descending', function () {
    $user = User::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $older = Conversation::factory()->withUsers($user, $userA)->create();
    Message::factory()->create([
        'conversation_id' => $older->id,
        'user_id' => $user->id,
        'created_at' => now()->subHour(),
    ]);

    $newer = Conversation::factory()->withUsers($user, $userB)->create();
    Message::factory()->create([
        'conversation_id' => $newer->id,
        'user_id' => $user->id,
        'created_at' => now(),
    ]);

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get();

    expect($result[0]->id)->toBe($newer->id)
        ->and($result[1]->id)->toBe($older->id);
});

test('paginate returns paginated results', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        $other = User::factory()->create();
        $conv = Conversation::factory()->withUsers($user, $other)->create();
        Message::factory()->create(['conversation_id' => $conv->id, 'user_id' => $user->id]);
    }

    $result = ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->paginate();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(20)
        ->and($result->count())->toBe(15);
});

test('conversation moves from requests to primary when user follows participant', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $stranger->id]);

    expect(ConversationListQuery::forUser($user->id)->tab(ConversationTab::Requests)->toQuery()->get())->toHaveCount(1);
    expect(ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get())->toHaveCount(0);

    $user->following()->attach($stranger);

    expect(ConversationListQuery::forUser($user->id)->tab(ConversationTab::Primary)->toQuery()->get())->toHaveCount(1);
    expect(ConversationListQuery::forUser($user->id)->tab(ConversationTab::Requests)->toQuery()->get())->toHaveCount(0);
});
