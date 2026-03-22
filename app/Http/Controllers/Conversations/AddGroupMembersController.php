<?php

namespace App\Http\Controllers\Conversations;

use App\Actions\Conversations\AddGroupMembers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\AddGroupMembersRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\User;

class AddGroupMembersController extends Controller
{
    public function __invoke(AddGroupMembersRequest $request, Conversation $conversation, AddGroupMembers $action): ConversationResource
    {
        $sender = $request->user();
        $newMembers = User::query()->whereIn('username', $request->validated('usernames'))->get();

        $action->execute($sender, $conversation, $newMembers, $request->boolean('include_history', true));

        $conversation->load(['users', 'latestMessage']);

        return ConversationResource::make($conversation);
    }
}
