<?php

namespace App\Actions\Conversations;

use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AddGroupMembers
{
    /**
     * @param  Collection<int, User>  $newMembers
     */
    public function execute(User $sender, Conversation $conversation, Collection $newMembers, bool $includeHistory): void
    {
        $pivotData = $includeHistory ? [] : ['visible_from' => now()];

        $conversation->users()->attach(
            $newMembers->pluck('id')->mapWithKeys(fn ($id) => [$id => $pivotData])
        );

        $names = $newMembers->pluck('public_name')->join(', ', ' and ');

        $conversation->messages()->create([
            'body' => "{$sender->public_name} added {$names}",
            'type' => MessageType::System,
        ]);
    }
}
