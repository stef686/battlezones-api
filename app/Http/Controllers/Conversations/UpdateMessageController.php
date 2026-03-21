<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\UpdateMessageRequest;
use App\Http\Resources\Conversations\MessageResource;
use App\Models\Conversation;
use App\Models\Message;

class UpdateMessageController extends Controller
{
    public function __invoke(UpdateMessageRequest $request, Conversation $conversation, Message $message): MessageResource
    {
        $message->update([
            'body' => $request->validated('body'),
            'edited_at' => now(),
        ]);

        return MessageResource::make($message);
    }
}
