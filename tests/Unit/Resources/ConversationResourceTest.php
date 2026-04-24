<?php

use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

test('dm conversation returns participants array with one element', function () {
    $authUser = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($authUser, $otherUser)->create();

    $request = Request::create('/');
    $request->setUserResolver(fn () => $authUser);

    $resource = ConversationResource::make($conversation->load('users'))->toArray($request);

    expect($resource['participants'])->toBeArray()
        ->toHaveCount(1)
        ->and($resource['participants'][0])->toBe([
            'id' => $otherUser->id,
            'public_name' => $otherUser->public_name,
            'username' => $otherUser->username,
        ]);
});

test('group conversation returns participants array with multiple elements', function () {
    $authUser = User::factory()->create();
    $memberA = User::factory()->create();
    $memberB = User::factory()->create();

    $conversation = Conversation::factory()->group('Test Group')->create();
    $conversation->users()->attach([$authUser->id, $memberA->id, $memberB->id]);

    $request = Request::create('/');
    $request->setUserResolver(fn () => $authUser);

    $resource = ConversationResource::make($conversation->load('users'))->toArray($request);

    expect($resource['participants'])->toBeArray()
        ->toHaveCount(2)
        ->and(collect($resource['participants'])->pluck('id')->all())
        ->toContain($memberA->id, $memberB->id);
});

test('response does not contain singular participant key', function () {
    $authUser = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($authUser, $otherUser)->create();

    $request = Request::create('/');
    $request->setUserResolver(fn () => $authUser);

    $resource = ConversationResource::make($conversation->load('users'))->toArray($request);

    expect($resource)->not->toHaveKey('participant');
});
