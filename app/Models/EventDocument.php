<?php

namespace App\Models;

use Database\Factories\EventDocumentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string $path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read string $url
 *
 * @method static \Database\Factories\EventDocumentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventDocument whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventDocument extends Model
{
    /** @use HasFactory<EventDocumentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'path',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk('public')->url($this->path));
    }
}
