<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StartConversationRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;

class StartConversationController extends Controller
{
    public function __invoke(StartConversationRequest $request): ConversationResource
    {
        $sender = $request->user();
        $recipient = User::findOrFail($request->validated('recipient_id'));

        $conversation = Conversation::findBetween($sender->id, $recipient->id);

        if ($conversation) {
            $conversation->users()->updateExistingPivot($sender->id, ['deleted_at' => null, 'archived_at' => null]);
            $conversation->users()->updateExistingPivot($recipient->id, ['deleted_at' => null]);
        } else {
            $conversation = Conversation::create();
            $conversation->users()->attach([$sender->id, $recipient->id]);
        }

        $message = $conversation->messages()->create([
            'body' => $request->validated('body'),
            'user_id' => $sender->id,
        ]);

        $recipientPivot = $conversation->users()->wherePivot('user_id', $recipient->id)->first();

        if (! $recipientPivot->pivot->getAttribute('archived_at')) {
            $recipient->notify(new NewMessageNotification($message, $sender));
        }

        $conversation->load(['users', 'latestMessage']);

        return ConversationResource::make($conversation);
    }
}
