<?php

namespace App\Http\Resources;

use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Reaction
 */
class ReactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
