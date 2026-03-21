<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at', 'deleted_at')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @return HasOne<Message, $this>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('users', function (Builder $q) use ($userId) {
            $q->where('conversation_user.user_id', $userId)
                ->whereNull('conversation_user.deleted_at');
        });
    }

    public static function findBetween(int $userA, int $userB): ?self
    {
        return self::query()
            ->whereHas('users', fn (Builder $q) => $q->where('conversation_user.user_id', $userA))
            ->whereHas('users', fn (Builder $q) => $q->where('conversation_user.user_id', $userB))
            ->first();
    }
}
