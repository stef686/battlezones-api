<?php

namespace App\Http\Resources\Events;

use App\Models\EventDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventDocument
 */
class EventDocumentResource extends JsonResource
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
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
