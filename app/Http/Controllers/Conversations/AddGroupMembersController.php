<?php

namespace App\Http\Controllers\Conversations;

use App\Actions\Conversations\AddGroupMembers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\AddGroupMembersRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Conversations', 'APIs for Conversations')]
class AddGroupMembersController extends Controller
{
    #[Endpoint('Add Group Members', 'Add new members to a group conversation.')]
    #[ResponseFromApiResource(ConversationResource::class, model: Conversation::class)]
    public function __invoke(AddGroupMembersRequest $request, Conversation $conversation, AddGroupMembers $action): ConversationResource
    {
        $sender = $request->user();
        $newMembers = User::query()->whereIn('id', $request->validated('recipient_ids'))->get();

        $action->execute($sender, $conversation, $newMembers, $request->boolean('include_history', true));

        $conversation->load(['users', 'latestMessage']);

        return ConversationResource::make($conversation);
    }
}
