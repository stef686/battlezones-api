<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\ConversationTab;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\ListConversationsRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListConversationsController extends Controller
{
    public function __invoke(ListConversationsRequest $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;
        $tab = ConversationTab::tryFrom($request->validated('tab', '')) ?? ConversationTab::Primary;

        $conversations = Conversation::query()
            ->forTab($tab, $userId)
            ->with(['users', 'latestMessage'])
            ->addSelect([
                'latest_message_at' => Message::query()
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->select('created_at')
                    ->limit(1),
                'unread_count' => Message::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('messages.conversation_id', 'conversations.id')
                    ->where('messages.user_id', '!=', $userId)
                    ->whereRaw(
                        'messages.created_at > coalesce((select cu.last_read_at from conversation_user cu where cu.conversation_id = conversations.id and cu.user_id = ?), \'1970-01-01\')',
                        [$userId]
                    ),
            ])
            ->orderByDesc('latest_message_at')
            ->paginate();

        return ConversationResource::collection($conversations);
    }
}
