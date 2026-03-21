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
        $recipientId = (int) $request->validated('recipient_id');

        $conversation = Conversation::findBetween($sender->id, $recipientId);

        if ($conversation) {
            // Resurface for sender if deleted
            $conversation->users()->updateExistingPivot($sender->id, ['deleted_at' => null]);
            // Resurface for recipient if deleted
            $conversation->users()->updateExistingPivot($recipientId, ['deleted_at' => null]);
        } else {
            $conversation = Conversation::create();
            $conversation->users()->attach([$sender->id, $recipientId]);
        }

        $message = $conversation->messages()->create([
            'body' => $request->validated('body'),
            'user_id' => $sender->id,
        ]);

        $recipient = User::find($recipientId);
        $recipient->notify(new NewMessageNotification($message, $sender));

        $conversation->load(['users', 'latestMessage']);

        return ConversationResource::make($conversation);
    }
}
