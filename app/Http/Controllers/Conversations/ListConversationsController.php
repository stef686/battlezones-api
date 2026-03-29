<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\ConversationTab;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\ListConversationsRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Conversations', 'APIs for Conversations')]
class ListConversationsController extends Controller
{
    private const EPOCH = '1970-01-01 00:00:00';

    #[Endpoint('List Conversations', 'List the authenticated user\'s conversations.')]
    #[ResponseFromApiResource(ConversationResource::class, model: Conversation::class, paginate: 15)]
    public function __invoke(ListConversationsRequest $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;
        $tab = ConversationTab::tryFrom($request->validated('tab', '')) ?? ConversationTab::Primary;

        $lastReadAt = DB::table('conversation_user')
            ->whereColumn('conversation_user.conversation_id', 'conversations.id')
            ->where('conversation_user.user_id', $userId)
            ->select('last_read_at')
            ->limit(1);

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
                    ->where('messages.created_at', '>', DB::raw(
                        'coalesce(('.$lastReadAt->toSql().'), ?)'
                    ))
                    ->addBinding([...$lastReadAt->getBindings(), self::EPOCH]),
            ])
            ->orderByDesc('latest_message_at')
            ->paginate();

        return ConversationResource::collection($conversations);
    }
}
