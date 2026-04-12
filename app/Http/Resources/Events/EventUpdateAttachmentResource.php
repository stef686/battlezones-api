<?php

namespace App\Http\Resources\Events;

use App\Models\EventUpdateAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventUpdateAttachment
 */
class EventUpdateAttachmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'display_order' => $this->display_order,
        ];
    }
}
