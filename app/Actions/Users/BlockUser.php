<?php

namespace App\Actions\Users;

use App\Models\Conversation;
use App\Models\User;

class BlockUser
{
    public function execute(User $blocker, User $target): void
    {
        $blocker->blockedUsers()->syncWithoutDetaching($target);

        $blocker->following()->detach($target);
        $blocker->followers()->detach($target);

        $conversation = Conversation::findBetween($blocker->id, $target->id);

        if ($conversation) {
            $conversation->users()->updateExistingPivot(
                [$blocker->id, $target->id],
                ['deleted_at' => now()],
            );
        }
    }
}
