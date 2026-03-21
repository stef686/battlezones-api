<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListConversationsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $conversations = Conversation::query()
            ->forUser($request->user()->id)
            ->with(['users', 'latestMessage'])
            ->addSelect(['latest_message_at' => Message::query()
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->select('created_at')
                ->limit(1),
            ])
            ->orderByDesc('latest_message_at')
            ->paginate();

        return ConversationResource::collection($conversations);
    }
}
