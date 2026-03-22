<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\MessageType;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class LeaveGroupConversationController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation): Response
    {
        Gate::authorize('leave', $conversation);

        $user = $request->user();

        $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

        $conversation->messages()->create([
            'body' => "{$user->public_name} left the group",
            'type' => MessageType::System,
        ]);

        return response()->noContent();
    }
}
