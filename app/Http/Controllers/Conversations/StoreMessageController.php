<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StoreMessageRequest;
use App\Http\Resources\Conversations\MessageResource;
use App\Models\Conversation;
use App\Notifications\Conversations\NewMessageNotification;

class StoreMessageController extends Controller
{
    public function __invoke(StoreMessageRequest $request, Conversation $conversation): MessageResource
    {
        $sender = $request->user();

        $message = $conversation->messages()->create([
            'body' => $request->validated('body'),
            'user_id' => $sender->id,
        ]);

        $recipient = $conversation->users()
            ->wherePivot('user_id', '!=', $sender->id)
            ->first();

        if ($recipient->pivot->getAttribute('deleted_at')) {
            $conversation->users()->updateExistingPivot($recipient->id, ['deleted_at' => null]);
        }

        $recipient->notify(new NewMessageNotification($message, $sender));

        return MessageResource::make($message);
    }
}
