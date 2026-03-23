<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\UpdateMessageRequest;
use App\Http\Resources\Conversations\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Conversations', 'APIs for Conversations')]
class UpdateMessageController extends Controller
{
    #[Endpoint('Update Message', 'Edit an existing message in a conversation.')]
    #[ResponseFromApiResource(MessageResource::class, model: Message::class)]
    public function __invoke(UpdateMessageRequest $request, Conversation $conversation, Message $message): MessageResource
    {
        $message->update([
            'body' => $request->validated('body'),
            'edited_at' => now(),
        ]);

        return MessageResource::make($message);
    }
}
