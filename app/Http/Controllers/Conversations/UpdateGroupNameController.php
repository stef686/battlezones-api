<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\MessageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\UpdateGroupNameRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;

class UpdateGroupNameController extends Controller
{
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
