<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $name
 * @property bool $is_group
 * @property int|null $event_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Message|null $latestMessage
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\ConversationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Conversation newModelQuery()
 * @method static Builder<static>|Conversation newQuery()
 * @method static Builder<static>|Conversation query()
 * @method static Builder<static>|Conversation whereCreatedAt($value)
 * @method static Builder<static>|Conversation whereEventId($value)
 * @method static Builder<static>|Conversation whereId($value)
 * @method static Builder<static>|Conversation whereIsGroup($value)
 * @method static Builder<static>|Conversation whereName($value)
 * @method static Builder<static>|Conversation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_group',
        'event_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
        ];
    }

    public function isGroup(): bool
    {
        return $this->is_group;
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at', 'visible_from', 'deleted_at', 'archived_at')
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

    public static function findBetween(int $userA, int $userB): ?self
    {
        return self::query()
            ->where('is_group', false)
            ->whereHas('users', fn (Builder $q) => $q->where('conversation_user.user_id', $userA))
            ->whereHas('users', fn (Builder $q) => $q->where('conversation_user.user_id', $userB))
            ->first();
    }
}
