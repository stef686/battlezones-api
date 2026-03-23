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
class DeleteConversationController extends Controller
{
    #[Endpoint('Delete Conversation', 'Delete a conversation for the authenticated user.')]
    #[Response(['message' => 'Conversation deleted.'])]
    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('delete', $conversation);

        $conversation->users()->updateExistingPivot($request->user()->id, [
            'deleted_at' => now(),
        ]);

        return response()->json(['message' => 'Conversation deleted.']);
    }
}
