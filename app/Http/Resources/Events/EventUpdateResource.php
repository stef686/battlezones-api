<?php

namespace App\Http\Resources\Events;

use App\Models\EventUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventUpdate
 */
class EventUpdateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'pinned' => $this->pinned_at !== null,
            'published_at' => $this->published_at->toIso8601ZuluString(),
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],
            'attachments' => EventUpdateAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
