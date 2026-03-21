<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UnarchiveConversationController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('unarchive', $conversation);

        $conversation->users()->updateExistingPivot($request->user()->id, [
            'archived_at' => null,
        ]);

        return response()->json(['message' => 'Conversation unarchived.']);
    }
}
