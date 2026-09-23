<?php

namespace App\Http\Resources\Events;

use App\Enums\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Country
 */
class CountryResource extends JsonResource
{
    /**
     * @return array{code: string, name: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->value,
            'name' => $this->label(),
        ];
    }
}
