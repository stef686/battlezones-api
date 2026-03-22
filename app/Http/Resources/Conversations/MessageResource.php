<?php

namespace App\Http\Resources\Conversations;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Message
 */
class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'user_id' => $this->user_id,
            'body' => $this->isDeleted() ? null : $this->body,
            'type' => $this->type?->value,
            'is_system' => $this->isSystem(),
            'is_deleted' => $this->isDeleted(),
            'is_edited' => $this->edited_at !== null,
            'is_editable' => $this->isEditable(),
            'edited_at' => $this->edited_at?->toIso8601ZuluString(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
