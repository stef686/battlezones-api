<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StoreMessageRequest;
use App\Http\Resources\Conversations\MessageResource;
use App\Models\Conversation;
use App\Notifications\Conversations\NewMessageNotification;
use Illuminate\Support\Facades\DB;

class StoreMessageController extends Controller
{
    public function __invoke(StoreMessageRequest $request, Conversation $conversation): MessageResource
    {
        $sender = $request->user();

        $message = $conversation->messages()->create([
            'body' => $request->validated('body'),
            'user_id' => $sender->id,
        ]);

        $recipient = $conversation->users()->where('user_id', '!=', $sender->id)->first();

        // Resurface conversation for recipient if they deleted it
        $recipientDeletedAt = DB::table('conversation_user')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $recipient->id)
            ->value('deleted_at');

        if ($recipientDeletedAt) {
            $conversation->users()->updateExistingPivot($recipient->id, ['deleted_at' => null]);
        }

        $recipient->notify(new NewMessageNotification($message, $sender));

        return MessageResource::make($message);
    }
}
