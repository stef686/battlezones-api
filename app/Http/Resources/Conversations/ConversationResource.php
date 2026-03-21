<?php

namespace App\Http\Resources\Conversations;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Conversation
 */
class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = $request->user();
        $participant = $this->users->firstWhere('id', '!=', $authUser->id);

        $lastReadAt = DB::table('conversation_user')
            ->where('conversation_id', $this->id)
            ->where('user_id', $authUser->id)
            ->value('last_read_at');

        return [
            'id' => $this->id,
            'participant' => $participant ? [
                'id' => $participant->id,
                'public_name' => $participant->public_name,
                'username' => $participant->username,
            ] : null,
            'latest_message' => $this->whenLoaded('latestMessage', fn () => $this->latestMessage
                ? MessageResource::make($this->latestMessage)
                : null
            ),
            'unread_count' => $lastReadAt
                ? $this->messages()->where('created_at', '>', $lastReadAt)->where('user_id', '!=', $authUser->id)->count()
                : $this->messages()->where('user_id', '!=', $authUser->id)->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
