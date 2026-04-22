<?php

namespace App\Models;

use Database\Factories\EventUpdateFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property string|null $title
 * @property string $body
 * @property Carbon|null $pinned_at
 * @property Carbon $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, EventUpdateAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read User $author
 * @property-read Event $event
 *
 * @method static \Database\Factories\EventUpdateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate wherePinnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdate whereUserId($value)
 *
 * @mixin \Eloquent
 */
class EventUpdate extends Model
{
    /** @use HasFactory<EventUpdateFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'title',
        'body',
        'pinned_at',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pinned_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<EventUpdateAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(EventUpdateAttachment::class)->orderBy('display_order');
    }
}
