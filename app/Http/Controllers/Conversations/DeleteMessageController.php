<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DeleteMessageController extends Controller
{
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
