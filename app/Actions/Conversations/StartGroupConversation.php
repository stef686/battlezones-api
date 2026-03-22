<?php

namespace App\Actions\Conversations;

use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;
use Illuminate\Database\Eloquent\Collection;

class StartGroupConversation
{
    /**
     * @param  Collection<int, User>  $members
     */
    public function execute(User $sender, Collection $members, string $name, string $body): Conversation
    {
        $conversation = Conversation::create([
            'name' => $name,
            'is_group' => true,
        ]);

        $conversation->users()->attach(
            $members->pluck('id')->push($sender->id)
        );

        $message = $conversation->messages()->create([
            'body' => $body,
            'user_id' => $sender->id,
        ]);

        $conversation->messages()->create([
            'body' => "{$sender->public_name} created the group",
            'type' => MessageType::System,
        ]);

        foreach ($members as $member) {
            $member->notify(new NewMessageNotification($message, $sender));
        }

        $conversation->load(['users', 'latestMessage']);

        return $conversation;
    }
}
