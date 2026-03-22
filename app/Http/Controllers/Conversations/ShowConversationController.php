<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Resources\Conversations\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ShowConversationController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation): AnonymousResourceCollection
    {
        Gate::authorize('view', $conversation);

        $userId = $request->user()->id;
        $pivot = DB::table('conversation_user')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->first(['deleted_at', 'visible_from']);

        $messages = $conversation->messages()
            ->when($pivot?->deleted_at, fn ($q, $deletedAt) => $q->where('created_at', '>', $deletedAt))
            ->when($pivot?->visible_from, fn ($q, $visibleFrom) => $q->where('created_at', '>=', $visibleFrom))
            ->oldest('created_at')
            ->paginate();

        $conversation->users()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        return MessageResource::collection($messages);
    }
}
