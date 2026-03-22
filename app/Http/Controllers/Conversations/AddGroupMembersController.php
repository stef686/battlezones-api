<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\MessageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\AddGroupMembersRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\User;

class AddGroupMembersController extends Controller
{
    public function __invoke(AddGroupMembersRequest $request, Conversation $conversation): ConversationResource
    {
        $sender = $request->user();
        $includeHistory = $request->boolean('include_history', true);
        $newMembers = User::query()->whereIn('username', $request->validated('usernames'))->get();

        $pivotData = $includeHistory ? [] : ['visible_from' => now()];

        $conversation->users()->attach(
            $newMembers->pluck('id')->mapWithKeys(fn ($id) => [$id => $pivotData])
        );

        $names = $newMembers->pluck('public_name')->join(', ', ' and ');

        $conversation->messages()->create([
            'body' => "{$sender->public_name} added {$names}",
            'type' => MessageType::System,
        ]);

        $conversation->load(['users', 'latestMessage']);

        return ConversationResource::make($conversation);
    }
}
