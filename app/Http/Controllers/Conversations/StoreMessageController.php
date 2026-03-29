<?php

namespace App\Http\Controllers\Conversations;

use App\Actions\Conversations\SendMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StoreMessageRequest;
use App\Http\Resources\Conversations\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Conversations', 'APIs for Conversations')]
class StoreMessageController extends Controller
{
    #[Endpoint('Send Message', 'Send a new message in a conversation.')]
    #[ResponseFromApiResource(MessageResource::class, model: Message::class)]
    public function __invoke(StoreMessageRequest $request, Conversation $conversation, SendMessage $sendMessage): MessageResource
    {
        $message = $sendMessage->execute(
            sender: $request->user(),
            conversation: $conversation,
            body: $request->validated('body'),
        );

        return MessageResource::make($message);
    }
}
