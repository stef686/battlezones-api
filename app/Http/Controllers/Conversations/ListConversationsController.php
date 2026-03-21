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
            ->tap(fn ($q) => match ($tab) {
                ConversationTab::Primary => $q->primaryForUser($userId),
                ConversationTab::Events => $q->eventsForUser($userId),
                ConversationTab::Requests => $q->requestsForUser($userId),
                ConversationTab::Archived => $q->archivedForUser($userId),
            })
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
