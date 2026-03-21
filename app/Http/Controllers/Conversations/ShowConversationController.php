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

        $deletedAt = DB::table('conversation_user')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $request->user()->id)
            ->value('deleted_at');

        $messages = $conversation->messages()
            ->when($deletedAt, fn ($q) => $q->where('created_at', '>', $deletedAt))
            ->oldest('created_at')
            ->paginate();

        $conversation->users()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return MessageResource::collection($messages);
    }
}
