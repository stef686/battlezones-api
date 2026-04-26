<?php

namespace App\Http\Resources\Conversations;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Conversation
 *
 * @property int $unread_count
 */
class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = $request->user();
        $authUserModel = $authUser ? $this->users->firstWhere('id', $authUser->id) : null;
        /** @var Pivot|null $authPivot */
        $authPivot = $authUserModel?->getRelation('pivot');
        $otherUsers = $authUser ? $this->users->where('id', '!=', $authUser->id) : $this->users;

        return [
            'id' => $this->id,
            'is_group' => $this->is_group,
            'name' => $this->name,
            'participants' => $otherUsers->map->only('id', 'public_name', 'username')->values()->all(),
            'latest_message' => $this->whenLoaded('latestMessage', fn () => $this->latestMessage
                ? MessageResource::make($this->latestMessage)
                : null
            ),
            'is_archived' => $authPivot?->getAttribute('archived_at') !== null,
            'unread_count' => (int) $this->unread_count,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
