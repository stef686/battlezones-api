<?php

namespace App\Actions\Conversations;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SendMessage
{
    public function execute(User $sender, Conversation $conversation, string $body): Message
    {
        $message = $conversation->messages()->create([
            'body' => $body,
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

        return $message;
    }
}
