<?php

namespace App\Actions\Conversations;

use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\User;

class RemoveGroupMember
{
    public function execute(User $sender, Conversation $conversation, User $member): void
    {
        $conversation->users()->updateExistingPivot($member->id, ['deleted_at' => now()]);

        $conversation->messages()->create([
            'body' => "{$sender->public_name} removed {$member->public_name}",
            'type' => MessageType::System,
        ]);
    }
}
