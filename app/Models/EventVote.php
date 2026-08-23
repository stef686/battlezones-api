<?php

namespace App\Models;

use Database\Factories\EventVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One pick within one Player's Ballot.
 *
 * @property int $id
 * @property int $event_poll_id
 * @property int $voter_user_id
 * @property int $subject_event_attendee_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EventPoll $poll
 * @property-read EventAttendee $subject
 * @property-read User $voter
 *
 * @method static \Database\Factories\EventVoteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote whereEventPollId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote whereSubjectEventAttendeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventVote whereVoterUserId($value)
 *
 * @mixin \Eloquent
 */
class EventVote extends Model
{
    /** @use HasFactory<EventVoteFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_poll_id',
        'voter_user_id',
        'subject_event_attendee_id',
    ];

    /**
     * @return BelongsTo<EventPoll, $this>
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(EventPoll::class, 'event_poll_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_user_id');
    }

    /**
     * @return BelongsTo<EventAttendee, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(EventAttendee::class, 'subject_event_attendee_id');
    }
}
