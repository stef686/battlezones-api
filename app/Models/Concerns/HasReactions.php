<?php

namespace App\Models\Concerns;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReactions
{
    /**
     * @return MorphMany<Reaction, $this>
     */
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }
}
