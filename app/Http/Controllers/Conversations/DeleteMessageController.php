<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Conversations', 'APIs for Conversations')]
class DeleteMessageController extends Controller
{
    #[Endpoint('Delete Message', 'Soft-delete a message in a conversation.')]
    #[Response(['message' => 'Message deleted.'])]
    public function __invoke(Conversation $conversation, Message $message): JsonResponse
    {
        Gate::authorize('delete', $message);

        $message->update([
            'body' => null,
            'deleted_at' => now(),
        ]);

        return response()->json(['message' => 'Message deleted.']);
    }
}
