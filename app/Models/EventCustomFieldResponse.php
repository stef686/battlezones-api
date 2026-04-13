<?php

namespace App\Models;

use Database\Factories\EventCustomFieldResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_attendee_id
 * @property int $event_custom_field_id
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EventAttendee $attendee
 * @property-read EventCustomField $field
 *
 * @method static EventCustomFieldResponseFactory factory($count = null, $state = [])
 */
class EventCustomFieldResponse extends Model
{
    /** @use HasFactory<EventCustomFieldResponseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_attendee_id',
        'event_custom_field_id',
        'value',
    ];

    /**
     * @return BelongsTo<EventAttendee, $this>
     */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(EventAttendee::class, 'event_attendee_id');
    }

    /**
     * @return BelongsTo<EventCustomField, $this>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(EventCustomField::class, 'event_custom_field_id');
    }
}
