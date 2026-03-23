<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\MessageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\UpdateGroupNameRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Conversations', 'APIs for Conversations')]
class UpdateGroupNameController extends Controller
{
    #[Endpoint('Update Group Name', 'Update the name of a group conversation.')]
    #[ResponseFromApiResource(ConversationResource::class, model: Conversation::class)]
    public function __invoke(UpdateGroupNameRequest $request, Conversation $conversation): ConversationResource
    {
        $user = $request->user();
        $newName = $request->validated('name');

        $conversation->update(['name' => $newName]);

        $conversation->messages()->create([
            'body' => "{$user->public_name} renamed the group to {$newName}",
            'type' => MessageType::System,
        ]);

        $conversation->load(['users', 'latestMessage']);

        return ConversationResource::make($conversation);
    }
}
