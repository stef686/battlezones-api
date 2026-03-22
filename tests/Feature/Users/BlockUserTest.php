<?php

use App\Models\Conversation;
use App\Models\User;

test('a user can block another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('users.block', $target))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'id' => $target->id,
                'is_blocked_by_you' => true,
            ],
        ]);

    $this->assertDatabaseHas('blocks', [
        'blocker_id' => $user->id,
        'blocked_id' => $target->id,
    ]);
});

test('a user cannot block themselves', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('users.block', $user))
        ->assertForbidden();
});

test('blocking is idempotent', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('users.block', $target))->assertSuccessful();
    $this->postJson(route('users.block', $target))->assertSuccessful();

    expect($user->blockedUsers()->count())->toBe(1);
});

test('blocking removes mutual follows', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->following()->attach($target);
    $target->following()->attach($user);
    $this->actingAs($user);

    $this->postJson(route('users.block', $target))->assertSuccessful();

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'following_id' => $target->id,
    ]);
    $this->assertDatabaseMissing('follows', [
        'follower_id' => $target->id,
        'following_id' => $user->id,
    ]);
});

test('a user can unblock another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->blockedUsers()->attach($target);
    $this->actingAs($user);

    $this->deleteJson(route('users.unblock', $target))
        ->assertSuccessful();

    $this->assertDatabaseMissing('blocks', [
        'blocker_id' => $user->id,
        'blocked_id' => $target->id,
    ]);
});

test('unblocking a non-blocked user is idempotent', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($user);

    $this->deleteJson(route('users.unblock', $target))
        ->assertSuccessful();
});

test('blocked user cannot view blocker profile', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();
    $blocker->blockedUsers()->attach($blocked);
    $this->actingAs($blocked);

    $this->getJson(route('profile.show', $blocker))
        ->assertForbidden();
});

test('blocker can view blocked user profile with is_blocked_by_you true', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();
    $blocker->blockedUsers()->attach($blocked);
    $this->actingAs($blocker);

    $this->getJson(route('profile.show', $blocked))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'is_blocked_by_you' => true,
            ],
        ]);
});

test('blocked user cannot follow blocker', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();
    $blocker->blockedUsers()->attach($blocked);
    $this->actingAs($blocked);

    $this->postJson(route('users.follow', $blocker))
        ->assertForbidden();
});

test('blocker cannot follow blocked user', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();
    $blocker->blockedUsers()->attach($blocked);
    $this->actingAs($blocker);

    $this->postJson(route('users.follow', $blocked))
        ->assertForbidden();
});

test('blocked users are excluded from search in both directions', function () {
    $user = User::factory()->create();
    $blocked = User::factory()->create(['username' => 'searchable_one']);
    $blockedBy = User::factory()->create(['username' => 'searchable_two']);
    $visible = User::factory()->create(['username' => 'searchable_three']);

    $user->blockedUsers()->attach($blocked);
    $blockedBy->blockedUsers()->attach($user);

    $this->actingAs($user);

    $response = $this->getJson(route('users.search', ['q' => 'searchable']))
        ->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($visible->id)
        ->not->toContain($blocked->id)
        ->not->toContain($blockedBy->id);
});

test('blocked users are excluded from followers list', function () {
    $user = User::factory()->create();
    $blocked = User::factory()->create();
    $normal = User::factory()->create();

    $blocked->following()->attach($user);
    $normal->following()->attach($user);
    $user->blockedUsers()->attach($blocked);

    $this->actingAs($user);

    $response = $this->getJson(route('users.followers', $user))
        ->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($normal->id)
        ->not->toContain($blocked->id);
});

test('blocked users are excluded from following list', function () {
    $user = User::factory()->create();
    $blocked = User::factory()->create();
    $normal = User::factory()->create();

    $user->following()->attach($blocked);
    $user->following()->attach($normal);
    $user->blockedUsers()->attach($blocked);

    $this->actingAs($user);

    $response = $this->getJson(route('users.following', $user))
        ->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($normal->id)
        ->not->toContain($blocked->id);
});

test('blocked users cannot message each other', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();
    $blocker->blockedUsers()->attach($blocked);

    $this->actingAs($blocker)
        ->postJson(route('conversations.store'), [
            'recipient_id' => $blocked->id,
            'body' => 'Hello!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_id');

    $this->actingAs($blocked)
        ->postJson(route('conversations.store'), [
            'recipient_id' => $blocker->id,
            'body' => 'Hello!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_id');
});

test('blocking soft-deletes direct conversation between users', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $conversation = Conversation::factory()->withUsers($user, $target)->create();
    $this->actingAs($user);

    $this->postJson(route('users.block', $target))->assertSuccessful();

    $pivotUser = $conversation->users()->where('user_id', $user->id)->first();
    $pivotTarget = $conversation->users()->where('user_id', $target->id)->first();

    expect($pivotUser->pivot->deleted_at)->not->toBeNull();
    expect($pivotTarget->pivot->deleted_at)->not->toBeNull();
});

test('blocked users list returns paginated results', function () {
    $user = User::factory()->create();
    $blocked1 = User::factory()->create();
    $blocked2 = User::factory()->create();
    $user->blockedUsers()->attach([$blocked1->id, $blocked2->id]);
    $this->actingAs($user);

    $response = $this->getJson(route('blocked-users.index'))
        ->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($blocked1->id)
        ->toContain($blocked2->id);
});

test('blocked users list is empty when none blocked', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->getJson(route('blocked-users.index'))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
