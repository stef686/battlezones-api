<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\MessageType;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ScribeResponse;

#[Group('Conversations', 'APIs for Conversations')]
class LeaveGroupConversationController extends Controller
{
    #[Endpoint('Leave Group Conversation', 'Leave a group conversation.')]
    #[ScribeResponse(content: '', status: 204)]
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
