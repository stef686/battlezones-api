<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeleteMessageController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation, Message $message): JsonResponse
    {
        Gate::authorize('delete', $message);

        $message->update([
            'body' => null,
            'deleted_at' => now(),
        ]);

        return response()->json(['message' => 'Message deleted.']);
    }
}
