<?php

namespace App\Models\Concerns;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Scope to include reactions_count and has_reacted for a given user.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithReactionData(Builder $query, int $userId): Builder
    {
        return $query
            ->withCount('reactions')
            ->withExists(['reactions as has_reacted' => fn (Builder $q) => $q->where('user_id', $userId)]);
    }

    /**
     * Eager-load reactions_count and has_reacted onto an existing model instance.
     */
    public function loadReactionData(int $userId): static
    {
        return $this->loadCount('reactions')
            ->loadExists(['reactions as has_reacted' => fn (Builder $q) => $q->where('user_id', $userId)]);
    }
}
