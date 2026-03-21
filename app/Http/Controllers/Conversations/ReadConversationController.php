<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReadConversationController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        $conversation->users()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return response()->json(['message' => 'Conversation marked as read.']);
    }
}
