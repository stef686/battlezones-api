<?php

namespace App\Actions\Conversations;

use App\Models\Conversation;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;
use App\Services\PrivacyService;
use Illuminate\Validation\ValidationException;

class StartConversation
{
    public function __construct(private PrivacyService $privacyService) {}

    public function execute(User $sender, User $recipient, string $body): Conversation
    {
        if (! $this->privacyService->canMessage($sender, $recipient)) {
            throw ValidationException::withMessages([
                'recipient_ids' => ['You cannot message this user.'],
            ]);
        }

        $conversation = Conversation::findBetween($sender->id, $recipient->id);

        if ($conversation) {
            $conversation->users()->updateExistingPivot($sender->id, ['deleted_at' => null, 'archived_at' => null]);
            $conversation->users()->updateExistingPivot($recipient->id, ['deleted_at' => null]);
        } else {
            $conversation = Conversation::create();
            $conversation->users()->attach([$sender->id, $recipient->id]);
        }

        $message = $conversation->messages()->create([
            'body' => $body,
            'user_id' => $sender->id,
        ]);

        $recipientPivot = $conversation->users()
            ->wherePivot('user_id', $recipient->id)
            ->first();

        if (! $recipientPivot?->pivot->getAttribute('archived_at')) {
            $recipient->notify(new NewMessageNotification($message, $sender));
        }

        $conversation->load(['users', 'latestMessage']);

        return $conversation;
    }
}
