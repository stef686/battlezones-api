<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivotNull('deleted_at')
            ->exists();
    }

    public function delete(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
