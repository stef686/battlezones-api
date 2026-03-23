<?php

namespace App\Http\Controllers\Conversations;

use App\Actions\Conversations\StartConversation;
use App\Actions\Conversations\StartGroupConversation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StartConversationRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Conversations', 'APIs for Conversations')]
class StartConversationController extends Controller
{
    #[Endpoint('Start Conversation', 'Start a new direct or group conversation.')]
    #[ResponseFromApiResource(ConversationResource::class, model: Conversation::class)]
    public function __invoke(StartConversationRequest $request, StartConversation $startConversation, StartGroupConversation $startGroupConversation): ConversationResource
    {
        $sender = $request->user();
        $recipientIds = $request->validated('recipient_ids');

        if ($request->isGroupConversation()) {
            $members = User::query()->whereIn('id', $recipientIds)->get();
            $conversation = $startGroupConversation->execute($sender, $members, $request->validated('name'), $request->validated('body'));
        } else {
            $recipient = User::findOrFail($recipientIds[0]);
            $conversation = $startConversation->execute($sender, $recipient, $request->validated('body'));
        }

        return ConversationResource::make($conversation);
    }
}
