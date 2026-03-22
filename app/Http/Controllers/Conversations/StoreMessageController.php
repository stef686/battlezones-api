<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StoreMessageRequest;
use App\Http\Resources\Conversations\MessageResource;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StoreMessageController extends Controller
{
    public function __invoke(StoreMessageRequest $request, Conversation $conversation): MessageResource
    {
        $sender = $request->user();

        $message = $conversation->messages()->create([
            'body' => $request->validated('body'),
            'user_id' => $sender->id,
        ]);

        $otherMembers = $conversation->users()
            ->wherePivot('user_id', '!=', $sender->id)
            ->get();

        /** @var User&object{pivot: Pivot} $member */
        foreach ($otherMembers as $member) {
            if ($member->pivot->getAttribute('deleted_at')) {
                $conversation->users()->updateExistingPivot($member->id, ['deleted_at' => null]);
            }

            if (! $member->pivot->getAttribute('archived_at')) {
                $member->notify(new NewMessageNotification($message, $sender));
            }
        }

        return MessageResource::make($message);
    }
}
