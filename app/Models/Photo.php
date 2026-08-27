<?php

namespace App\Models;

use App\Models\Concerns\HasReactions;
use App\Services\UploadStorage;
use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string $path
 * @property string|null $thumbnail_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $event_id
 * @property-read Event|null $event
 * @property-read Collection<int, Reaction> $reactions
 * @property-read int|null $reactions_count
 * @property-read string|null $thumbnail_url
 * @property-read string $url
 * @property-read User $user
 *
 * @method static \Database\Factories\PhotoFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Photo withReactionData(int $userId)
 *
 * @mixin \Eloquent
 */
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    use HasReactions;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'path',
        'thumbnail_path',
        'user_id',
        'event_id',
    ];

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => UploadStorage::url($this->path));
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->thumbnail_path
            ? UploadStorage::url($this->thumbnail_path)
            : null);
    }
}
