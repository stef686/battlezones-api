<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Conversations', 'APIs for Conversations')]
class ReadConversationController extends Controller
{
    #[Endpoint('Mark Conversation as Read', 'Mark a conversation as read for the authenticated user.')]
    #[Response(['message' => 'Conversation marked as read.'])]
    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        $conversation->users()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return response()->json(['message' => 'Conversation marked as read.']);
    }
}
