<?php

namespace App\Http\Controllers\Conversations;

use App\Actions\Conversations\StartGroupConversation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StartGroupConversationRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\User;

class StartGroupConversationController extends Controller
{
    public function __invoke(StartGroupConversationRequest $request, StartGroupConversation $action): ConversationResource
    {
        $sender = $request->user();
        $members = User::query()->whereIn('username', $request->validated('usernames'))->get();

        $conversation = $action->execute($sender, $members, $request->validated('name'), $request->validated('body'));

        return ConversationResource::make($conversation);
    }
}
