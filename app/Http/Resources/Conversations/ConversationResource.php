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
        $pivot = DB::table('conversation_user')
            ->where('conversation_id', $this->id)
            ->where('user_id', $authUser->id)
            ->first(['last_read_at', 'archived_at']);
        $participant = $this->users->firstWhere('id', '!=', $authUser->id);

        $lastReadAt = $pivot?->last_read_at;
        $unreadQuery = $this->messages()->where('user_id', '!=', $authUser->id);

        if ($lastReadAt) {
            $unreadQuery->where('created_at', '>', $lastReadAt);
        }

        $otherUsers = $this->users->where('id', '!=', $authUser->id);

        return [
            'id' => $this->id,
            'is_group' => $this->is_group,
            'name' => $this->name,
            'participant' => ! $this->is_group ? $otherUsers->first()?->only('id', 'public_name', 'username') : null,
            'participants' => $this->is_group ? $otherUsers->map->only('id', 'public_name', 'username')->values() : null,
            'latest_message' => $this->whenLoaded('latestMessage', fn () => $this->latestMessage
                ? MessageResource::make($this->latestMessage)
                : null
            ),
            'is_archived' => $pivot?->archived_at !== null,
            'unread_count' => $unreadQuery->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
