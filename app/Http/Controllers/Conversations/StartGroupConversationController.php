<?php

namespace App\Http\Controllers\Conversations;

use App\Enums\MessageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StartGroupConversationRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;

class StartGroupConversationController extends Controller
{
    public function __invoke(StartGroupConversationRequest $request): ConversationResource
    {
        $sender = $request->user();
        $members = User::query()->whereIn('username', $request->validated('usernames'))->get();

        $conversation = Conversation::create([
            'name' => $request->validated('name'),
            'is_group' => true,
        ]);

        $conversation->users()->attach(
            $members->pluck('id')->push($sender->id)
        );

        $message = $conversation->messages()->create([
            'body' => $request->validated('body'),
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

        return ConversationResource::make($conversation);
    }
}
