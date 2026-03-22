<?php

namespace App\Http\Controllers\Conversations;

use App\Actions\Conversations\StartConversation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StartConversationRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\User;

class StartConversationController extends Controller
{
    public function __invoke(StartConversationRequest $request, StartConversation $action): ConversationResource
    {
        $sender = $request->user();
        $recipient = User::findOrFail($request->validated('recipient_id'));

        $conversation = $action->execute($sender, $recipient, $request->validated('body'));

        return ConversationResource::make($conversation);
    }
}
