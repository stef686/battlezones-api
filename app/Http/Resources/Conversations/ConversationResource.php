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
        $lastReadAt = DB::table('conversation_user')
            ->where('conversation_id', $this->id)
            ->where('user_id', $authUser->id)
            ->value('last_read_at');
        $participant = $this->users->firstWhere('id', '!=', $authUser->id);

        $unreadQuery = $this->messages()->where('user_id', '!=', $authUser->id);

        if ($lastReadAt) {
            $unreadQuery->where('created_at', '>', $lastReadAt);
        }

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
            'unread_count' => $unreadQuery->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
