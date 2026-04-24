<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\ConversationTab;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\ListConversationsRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Queries\ConversationListQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Conversations', 'APIs for Conversations')]
class ListConversationsController extends Controller
{
    #[Endpoint('List Conversations', 'List the authenticated user\'s conversations.')]
    #[ResponseFromApiResource(ConversationResource::class, model: Conversation::class, paginate: 15)]
    public function __invoke(ListConversationsRequest $request): AnonymousResourceCollection
    {
        $tab = ConversationTab::tryFrom($request->validated('tab', '')) ?? ConversationTab::Primary;

        $conversations = ConversationListQuery::forUser($request->user()->id)
            ->tab($tab)
            ->paginate();

        return ConversationResource::collection($conversations);
    }
}
