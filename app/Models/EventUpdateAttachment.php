<?php

namespace App\Models;

use App\Services\UploadStorage;
use Database\Factories\EventUpdateAttachmentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_update_id
 * @property string $name
 * @property string $path
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EventUpdate $eventUpdate
 * @property-read string $url
 *
 * @method static \Database\Factories\EventUpdateAttachmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment whereEventUpdateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventUpdateAttachment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventUpdateAttachment extends Model
{
    /** @use HasFactory<EventUpdateAttachmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_update_id',
        'name',
        'path',
        'display_order',
    ];

    /**
     * @return BelongsTo<EventUpdate, $this>
     */
    public function eventUpdate(): BelongsTo
    {
        return $this->belongsTo(EventUpdate::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => UploadStorage::url($this->path));
    }
}
